<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AiAssistant from '@/components/AI/AiAssistant.vue';
import axios from 'axios';
import { useDocumentStore } from '@/stores/document';
import MinimalCV from '@/components/Templates/CV/Minimal.vue';
import ModernCV from '@/components/Templates/CV/Modern.vue';
import ClassicInvoice from '@/components/Templates/Invoice/Classic.vue';
import CoverLetterDefault from '@/components/Templates/CoverLetter/Default.vue';
import QuoteSimple from '@/components/Templates/Quote/Simple.vue';
import CertificateDefault from '@/components/Templates/Certificate/Default.vue';
import CvScore from '@/components/CvScore.vue';
import AtsAnalysis from '@/components/AtsAnalysis.vue';
import ShareBox from '@/components/ShareBox.vue';
import { useCvScore } from '@/composables/useCvScore';

const { score: cvScore, issues: cvIssues } = useCvScore(computed(() => store.currentDocument));

// Forme brute renvoyée par le backend (content/style en colonnes JSON séparées)
interface BackendDocument {
    id: number;
    title: string;
    type: string;
    template: string;
    content: {
        profile?: Record<string, unknown>;
        experiences?: unknown[];
        invoiceItems?: unknown[];
        invoiceMeta?: unknown;
        coverLetter?: unknown;
        certificate?: unknown;
    };
    style: Record<string, unknown>;
}

const props = defineProps<{
    document: BackendDocument | null;
    shareUrl?: string | null;
}>();

const store = useDocumentStore();
const activeTab = ref<'edit' | 'preview'>('edit');
const isSaving = ref(false);
const isUploadingPhoto = ref(false);
const isImportingCv = ref(false);

const importCv = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    isImportingCv.value = true;
    saveError.value = null;

    try {
        const formData = new FormData();
        formData.append('file', file);
        const { data } = await axios.post('/import-cv', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        const p = data.profile;
        store.currentDocument.profile = {
            fullName: p.fullName || '',
            title: p.title || '',
            email: p.email || '',
            phone: p.phone || '',
            location: p.location || '',
            summary: p.summary || '',
        };
        store.currentDocument.experiences = (p.experiences || []).map((e: any) => ({
            id: crypto.randomUUID(),
            company: e.company || '',
            position: e.position || '',
            startDate: e.startDate || '',
            endDate: e.endDate || '',
            description: e.description || '',
        }));

        saveSuccess.value = 'CV importé — vérifie et corrige les informations avant de sauvegarder.';
        setTimeout(() => { saveSuccess.value = null; }, 5000);
    } catch (e: any) {
        saveError.value = e.response?.data?.message ?? "L'import du CV a échoué.";
    } finally {
        isImportingCv.value = false;
        input.value = '';
    }
};
const saveError = ref<string | null>(null);
const documentId = ref<number | null>(null);

const saveSuccess = ref<string | null>(null);
const saveStatus = ref<'idle' | 'unsaved' | 'saving' | 'saved' | 'error'>('idle');
let autosaveTimer: ReturnType<typeof setTimeout> | null = null;
let hasLoadedInitialDocument = false;
const AUTOSAVE_DELAY_MS = 1500;

// watch (et non onMounted) car Inertia réutilise cette même instance de composant
// quand on est redirigé de /editor/new vers /editor/{id} après création : onMounted
// ne se redéclencherait pas et l'éditeur resterait figé sur l'état "nouveau document".
watch(
    () => props.document,
    (doc) => {
        if (!doc) {
            hasLoadedInitialDocument = true;
            return;
        }

        documentId.value = doc.id;
        store.setDocument({
            id: doc.id,
            title: doc.title,
            type: doc.type as never,
            template: doc.template,
            profile: (doc.content?.profile as never) ?? store.currentDocument.profile,
            experiences: (doc.content?.experiences as never) ?? [],
            invoiceItems: (doc.content?.invoiceItems as never) ?? [],
            invoiceMeta: (doc.content?.invoiceMeta as never) ?? store.currentDocument.invoiceMeta,
            coverLetter: (doc.content?.coverLetter as never) ?? store.currentDocument.coverLetter,
            certificate: (doc.content?.certificate as never) ?? store.currentDocument.certificate,
            style: (doc.style as never) ?? store.currentDocument.style,
        });

        // On attend le prochain "tick" réactif pour ne pas déclencher l'autosave
        // à cause de ce chargement initial (setDocument ci-dessus change aussi currentDocument).
        setTimeout(() => { hasLoadedInitialDocument = true; }, 0);
    },
    { immediate: true }
);

const buildPayload = () => ({
    title: store.currentDocument.title,
    type: store.currentDocument.type,
    template: store.currentDocument.template,
    content: {
        profile: store.currentDocument.profile,
        experiences: store.currentDocument.experiences,
        invoiceItems: store.currentDocument.invoiceItems,
        invoiceMeta: store.currentDocument.invoiceMeta,
        coverLetter: store.currentDocument.coverLetter,
        certificate: store.currentDocument.certificate,
    },
    style: store.currentDocument.style,
});

const save = () => {
    if (autosaveTimer) { clearTimeout(autosaveTimer); autosaveTimer = null; }

    isSaving.value = true;
    saveStatus.value = 'saving';
    saveError.value = null;
    saveSuccess.value = null;

    const onSuccess = () => {
        saveStatus.value = 'saved';
        saveSuccess.value = 'Document enregistré.';
        setTimeout(() => { saveSuccess.value = null; }, 3000);
    };

    if (documentId.value) {
        // Document existant : mise à jour
        router.put(`/documents/${documentId.value}`, buildPayload(), {
            preserveScroll: true,
            preserveState: true,
            onSuccess,
            onError: (errors) => {
                saveStatus.value = 'error';
                saveError.value = Object.values(errors)[0] as string ?? 'Erreur de sauvegarde.';
            },
            onFinish: () => { isSaving.value = false; },
        });
        return;
    }

    // Nouveau document : création, puis on bascule sur l'URL d'édition
    // pour que les sauvegardes suivantes utilisent PUT (comportement idempotent).
    router.post('/documents', buildPayload(), {
        preserveScroll: true,
        onSuccess,
        onError: (errors) => {
            saveStatus.value = 'error';
            saveError.value = Object.values(errors)[0] as string ?? 'Erreur de sauvegarde.';
        },
        onFinish: () => { isSaving.value = false; },
    });
};

// Autosave : toute modification du document déclenche une sauvegarde automatique
// après un temps mort (debounce), pour respecter le cahier des charges §9 sans
// spammer le serveur à chaque frappe.
watch(
    () => JSON.stringify(store.currentDocument),
    () => {
        if (!hasLoadedInitialDocument) return;

        saveStatus.value = 'unsaved';

        if (autosaveTimer) clearTimeout(autosaveTimer);
        autosaveTimer = setTimeout(() => {
            autosaveTimer = null;
            save();
        }, AUTOSAVE_DELAY_MS);
    }
);

const isGeneratingSummary = ref(false);

const generateSummary = async () => {
    isGeneratingSummary.value = true;
    saveError.value = null;

    try {
        const { data } = await axios.post('/ai/summary', {
            context: {
                profile: store.currentDocument.profile,
                experiences: store.currentDocument.experiences,
            },
        });
        store.currentDocument.profile!.summary = data.result;
    } catch (e: any) {
        saveError.value = e.response?.data?.message ?? "L'assistant IA n'est pas disponible pour le moment.";
    } finally {
        isGeneratingSummary.value = false;
    }
};

const exportPdf = () => {
    if (!documentId.value) return;

    // On force une sauvegarde immédiate avant d'exporter : sinon, si l'autosave
    // (debounce 1,5s) n'a pas encore eu le temps de tourner, le PDF exporté
    // correspondrait à l'ancienne version enregistrée en base, pas à l'écran.
    if (autosaveTimer) { clearTimeout(autosaveTimer); autosaveTimer = null; }

    isSaving.value = true;
    saveStatus.value = 'saving';

    router.put(`/documents/${documentId.value}`, buildPayload(), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            saveStatus.value = 'saved';
            window.open(`/documents/${documentId.value}/export`, '_blank');
        },
        onError: (errors) => {
            saveStatus.value = 'error';
            saveError.value = Object.values(errors)[0] as string ?? 'Erreur de sauvegarde avant export.';
        },
        onFinish: () => { isSaving.value = false; },
    });
};

const onPhotoSelected = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    isUploadingPhoto.value = true;
    saveError.value = null;

    try {
        const formData = new FormData();
        formData.append('file', file);
        if (store.currentDocument.profile?.photoUrl) {
            formData.append('previous_url', store.currentDocument.profile.photoUrl);
        }
        const { data } = await axios.post('/media', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        if (store.currentDocument.profile) {
            store.currentDocument.profile.photoUrl = data.url;
        }
    } catch {
        saveError.value = "Échec de l'envoi de la photo (format ou taille invalide, 2 Mo max).";
    } finally {
        isUploadingPhoto.value = false;
        input.value = '';
    }
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <!-- En-tête avec Sauvegarde -->
        <header class="bg-white border-b px-6 py-4 flex justify-between items-center">
            <input
                v-model="store.currentDocument.title"
                type="text"
                class="text-xl font-bold border-none focus:ring-0 focus:outline-none"
                placeholder="Titre du document..."
            />

            <div class="flex items-center space-x-3">
                <!-- Bascule mobile -->
                <div class="md:hidden flex space-x-1">
                    <button
                        @click="activeTab = 'edit'"
                        :class="activeTab === 'edit' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'"
                        class="px-3 py-1 rounded-md text-sm font-medium"
                    >
                        Éditer
                    </button>
                    <button
                        @click="activeTab = 'preview'"
                        :class="activeTab === 'preview' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'"
                        class="px-3 py-1 rounded-md text-sm font-medium"
                    >
                        Aperçu
                    </button>
                </div>

                <button
                    v-if="documentId"
                    @click="exportPdf"
                    class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-md font-medium text-sm transition"
                >
                    Exporter en PDF
                </button>

                <span class="text-xs text-gray-500 flex items-center gap-1 min-w-[140px]">
                    <span v-if="saveStatus === 'saving'">Enregistrement en cours…</span>
                    <span v-else-if="saveStatus === 'saved'" class="text-green-600">✓ Enregistré</span>
                    <span v-else-if="saveStatus === 'unsaved'">Modifications non enregistrées</span>
                    <span v-else-if="saveStatus === 'error'" class="text-red-600">Échec de l'enregistrement</span>
                </span>

                <button
                    @click="save"
                    :disabled="isSaving"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md font-medium text-sm transition disabled:opacity-50"
                >
                    {{ isSaving ? 'Enregistrement...' : 'Sauvegarder' }}
                </button>
            </div>
        </header>

        <p v-if="saveError" class="bg-red-50 text-red-700 text-sm px-6 py-2 border-b border-red-100">
            {{ saveError }}
        </p>
        <p v-if="saveSuccess" class="bg-green-50 text-green-700 text-sm px-6 py-2 border-b border-green-100">
            {{ saveSuccess }}
        </p>

        <!-- Corps Dual-Pane -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Formulaire (Panneau Gauche) -->
            <div :class="['w-full md:w-1/2 p-6 overflow-y-auto bg-white border-r space-y-6', activeTab === 'preview' ? 'hidden md:block' : 'block']">

                <!-- Sélection Type & Modèle -->
                <div class="border-b pb-4 space-y-3">
                    <h2 class="text-md font-semibold text-gray-800">Paramètres du Document</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Type de Document</label>
                        <select v-model="store.currentDocument.type" class="w-full text-sm rounded border-gray-300 mt-1">
                            <option value="cv">Curriculum Vitae (CV)</option>
                            <option value="cover_letter">Lettre de motivation</option>
                            <option value="invoice">Facture</option>
                            <option value="quote">Devis</option>
                            <option value="attestation">Attestation</option>
                            <option value="certificate">Certificat</option>
                        </select>
                    </div>

                    <div v-if="!documentId && store.currentDocument.type === 'cv'">
                        <label class="text-xs text-indigo-600 hover:underline font-medium cursor-pointer inline-flex items-center gap-1">
                            <input type="file" accept="application/pdf" class="hidden" @change="importCv" :disabled="isImportingCv" />
                            {{ isImportingCv ? 'Import en cours…' : '📄 Importer un CV existant (PDF)' }}
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Modèle (Template)</label>
                        <select v-model="store.currentDocument.template" class="w-full text-sm rounded border-gray-300 mt-1">
                            <option v-if="store.currentDocument.type === 'cv'" value="minimal">CV Minimal</option>
                            <option v-if="store.currentDocument.type === 'cv'" value="modern">CV Modern</option>
                            <option v-if="store.currentDocument.type === 'cover_letter'" value="default">Lettre standard</option>
                            <option v-if="store.currentDocument.type === 'invoice'" value="classic">Facture Classic</option>
                            <option v-if="store.currentDocument.type === 'quote'" value="simple">Devis Simple</option>
                            <option v-if="['attestation', 'certificate'].includes(store.currentDocument.type)" value="default">Modèle standard</option>
                        </select>
                    </div>
                </div>

                <!-- Informations Générales -->
                <div class="border-b pb-4 space-y-3">
                    <h2 class="text-md font-semibold text-gray-800">Informations Générales</h2>

                    <div v-if="store.currentDocument.type === 'cv'" class="flex items-center space-x-4">
                        <img
                            v-if="store.currentDocument.profile?.photoUrl"
                            :src="store.currentDocument.profile.photoUrl"
                            class="h-14 w-14 rounded-full object-cover border"
                            alt="Photo de profil"
                        />
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Photo de profil</label>
                            <input type="file" accept="image/png,image/jpeg,image/webp" @change="onPhotoSelected" class="text-xs" />
                            <p v-if="isUploadingPhoto" class="text-xs text-gray-400">Envoi en cours...</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom complet</label>
                        <input v-if="store.currentDocument.profile" v-model="store.currentDocument.profile.fullName" type="text" class="mt-1 w-full text-sm rounded border-gray-300" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Titre / Intitulé</label>
                        <input v-if="store.currentDocument.profile" v-model="store.currentDocument.profile.title" type="text" class="mt-1 w-full text-sm rounded border-gray-300" />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input v-if="store.currentDocument.profile" v-model="store.currentDocument.profile.email" type="email" class="mt-1 w-full text-sm rounded border-gray-300" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                            <input v-if="store.currentDocument.profile" v-model="store.currentDocument.profile.phone" type="text" class="mt-1 w-full text-sm rounded border-gray-300" />
                        </div>
                    </div>
                    <div class="mt-3" v-if="store.currentDocument.type === 'cv'">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">Résumé</label>
                            <div class="flex items-center gap-2">
                                <button @click="generateSummary" :disabled="isGeneratingSummary" type="button" class="text-xs text-indigo-600 hover:underline font-medium disabled:opacity-50">
                                    {{ isGeneratingSummary ? 'Génération…' : '✨ Générer avec l\'IA' }}
                                </button>
                                <AiAssistant v-model="store.currentDocument.profile!.summary" />
                            </div>
                        </div>
                        <textarea v-model="store.currentDocument.profile!.summary" rows="3" placeholder="Résumé professionnel..." class="mt-1 w-full text-sm rounded border-gray-300"></textarea>
                    </div>
                </div>

                <!-- Options de Style -->
                <div class="border-b pb-4">
                    <h2 class="text-md font-semibold text-gray-800 mb-3">Style & Couleurs</h2>
                    <div class="flex items-center space-x-4">
                        <label class="text-sm text-gray-600">Couleur principale :</label>
                        <input type="color" v-model="store.currentDocument.style.primaryColor" class="h-8 w-14 rounded border border-gray-300 cursor-pointer" />
                    </div>
                </div>

                <!-- Section Expériences (si Type === 'cv') -->
                <div v-if="store.currentDocument.type === 'cv'" class="space-y-4">
                    <div class="flex justify-between items-center">
                        <h2 class="text-md font-semibold text-gray-800">Expériences</h2>
                        <button @click="store.addEmptyExperience" class="text-xs bg-indigo-50 text-indigo-600 px-3 py-1 rounded font-medium">
                            + Ajouter
                        </button>
                    </div>

                    <div v-for="exp in store.currentDocument.experiences" :key="exp.id" class="p-4 border rounded-md bg-gray-50 space-y-3 relative">
                        <button @click="store.removeExperience(exp.id)" class="absolute top-2 right-2 text-red-500 text-xs">
                            Supprimer
                        </button>
                        <input v-model="exp.position" placeholder="Poste" class="w-full text-sm rounded border-gray-300" />
                        <input v-model="exp.company" placeholder="Entreprise" class="w-full text-sm rounded border-gray-300" />
                        <div class="grid grid-cols-2 gap-2">
                            <input v-model="exp.startDate" placeholder="Début (ex: Jan 2022)" class="text-sm rounded border-gray-300" />
                            <input v-model="exp.endDate" placeholder="Fin (ex: Présent)" class="text-sm rounded border-gray-300" />
                        </div>
                        <textarea v-model="exp.description" placeholder="Missions..." class="w-full text-sm rounded border-gray-300 rows-2"></textarea>
                        <AiAssistant v-model="exp.description" />
                    </div>
                </div>

                <div v-if="store.currentDocument.type === 'cv'" class="space-y-4">
                    <CvScore :score="cvScore" :issues="cvIssues" />
                    <AtsAnalysis :doc="store.currentDocument" />
                </div>

                <ShareBox v-if="documentId" :document-id="documentId" :share-url="props.shareUrl ?? null" />

                <!-- Section Attestation / Certificat -->
                <div v-if="['attestation', 'certificate'].includes(store.currentDocument.type) && store.currentDocument.certificate" class="border-b pb-4 space-y-3">
                    <h2 class="text-md font-semibold text-gray-800">{{ store.currentDocument.type === 'certificate' ? 'Certificat' : 'Attestation' }}</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom du bénéficiaire</label>
                        <input v-model="store.currentDocument.certificate.recipientName" type="text" class="mt-1 w-full text-sm rounded border-gray-300" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Objet</label>
                        <input v-model="store.currentDocument.certificate.purpose" type="text" placeholder="Attestation de travail, de stage, de formation..." class="mt-1 w-full text-sm rounded border-gray-300" />
                    </div>
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">Texte</label>
                            <AiAssistant v-model="store.currentDocument.certificate.body" />
                        </div>
                        <textarea v-model="store.currentDocument.certificate.body" rows="6" placeholder="Je soussigné(e)..." class="mt-1 w-full text-sm rounded border-gray-300"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date d'émission</label>
                            <input v-model="store.currentDocument.certificate.issueDate" type="date" class="mt-1 w-full text-sm rounded border-gray-300" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Signataire</label>
                            <input v-model="store.currentDocument.certificate.issuerName" type="text" class="mt-1 w-full text-sm rounded border-gray-300" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fonction du signataire</label>
                        <input v-model="store.currentDocument.certificate.issuerTitle" type="text" placeholder="Directeur RH, Responsable formation..." class="mt-1 w-full text-sm rounded border-gray-300" />
                    </div>
                </div>

                <!-- Section Lettre de motivation -->
                <div v-if="store.currentDocument.type === 'cover_letter' && store.currentDocument.coverLetter" class="border-b pb-4 space-y-3">
                    <h2 class="text-md font-semibold text-gray-800">Lettre de motivation</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Destinataire</label>
                        <input v-model="store.currentDocument.coverLetter.recipientName" type="text" placeholder="Nom du recruteur" class="mt-1 w-full text-sm rounded border-gray-300" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Entreprise</label>
                        <input v-model="store.currentDocument.coverLetter.recipientCompany" type="text" class="mt-1 w-full text-sm rounded border-gray-300" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Objet</label>
                        <input v-model="store.currentDocument.coverLetter.subject" type="text" placeholder="Candidature au poste de..." class="mt-1 w-full text-sm rounded border-gray-300" />
                    </div>
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">Corps de la lettre</label>
                            <AiAssistant v-model="store.currentDocument.coverLetter.body" />
                        </div>
                        <textarea v-model="store.currentDocument.coverLetter.body" rows="10" placeholder="Madame, Monsieur,..." class="mt-1 w-full text-sm rounded border-gray-300"></textarea>
                    </div>
                </div>

                <!-- Section Articles (si Type === 'invoice' ou 'quote') -->
                <div v-if="['invoice', 'quote'].includes(store.currentDocument.type)" class="space-y-4">
                    <div class="flex justify-between items-center">
                        <h2 class="text-md font-semibold text-gray-800">Articles / Prestations</h2>
                        <button @click="store.addEmptyInvoiceItem" class="text-xs bg-indigo-50 text-indigo-600 px-3 py-1 rounded font-medium">
                            + Ajouter une ligne
                        </button>
                    </div>

                    <div v-if="store.currentDocument.invoiceMeta" class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">TVA (%)</label>
                            <input
                                :value="store.currentDocument.invoiceMeta.taxRate * 100"
                                @input="store.currentDocument.invoiceMeta!.taxRate = Number(($event.target as HTMLInputElement).value) / 100"
                                type="number" min="0" max="100" step="0.5"
                                class="w-full text-sm rounded border-gray-300"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Devise</label>
                            <select v-model="store.currentDocument.invoiceMeta.currency" class="w-full text-sm rounded border-gray-300">
                                <option value="FCFA">FCFA</option>
                                <option value="EUR">EUR</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                    </div>

                    <div v-for="item in store.currentDocument.invoiceItems" :key="item.id" class="p-3 border rounded-md bg-gray-50 space-y-2 relative">
                        <button @click="store.removeInvoiceItem(item.id)" class="absolute top-2 right-2 text-red-500 text-xs">
                            Supprimer
                        </button>
                        <input v-model="item.description" placeholder="Description de l'article" class="w-full text-sm rounded border-gray-300" />
                        <div class="grid grid-cols-2 gap-2">
                            <input v-model.number="item.quantity" type="number" placeholder="Quantité" class="text-sm rounded border-gray-300" />
                            <input v-model.number="item.unitPrice" type="number" placeholder="Prix Unitaire (FCFA)" class="text-sm rounded border-gray-300" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Prévisualisation A4 (Panneau Droit) -->
            <div :class="['w-full md:w-1/2 p-6 overflow-y-auto bg-gray-200 flex justify-center items-start', activeTab === 'edit' ? 'hidden md:flex' : 'flex']">
                <div class="bg-white shadow-2xl p-8 aspect-[1/1.414] w-[210mm] max-w-full min-h-[297mm]">
                    <MinimalCV v-if="store.currentDocument.type === 'cv' && store.currentDocument.template === 'minimal'" :doc="store.currentDocument" />
                    <ModernCV v-if="store.currentDocument.type === 'cv' && store.currentDocument.template === 'modern'" :doc="store.currentDocument" />
                    <ClassicInvoice v-if="store.currentDocument.type === 'invoice' && store.currentDocument.template === 'classic'" :doc="store.currentDocument" />
                    <CoverLetterDefault v-if="store.currentDocument.type === 'cover_letter' && store.currentDocument.template === 'default'" :doc="store.currentDocument" />
                    <QuoteSimple v-if="store.currentDocument.type === 'quote' && store.currentDocument.template === 'simple'" :doc="store.currentDocument" />
                    <CertificateDefault v-if="['attestation', 'certificate'].includes(store.currentDocument.type)" :doc="store.currentDocument" />
                </div>
            </div>
        </div>
    </div>
</template>