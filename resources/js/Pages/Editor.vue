<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AiAssistant from '@/components/AI/AiAssistant.vue';
import axios from 'axios';
import { useDocumentStore } from '@/stores/document';
import MonochromeCV from '@/components/Templates/CV/Monochrome.vue';
import BannerPhotoCV from '@/components/Templates/CV/BannerPhoto.vue';
import DarkSidebarCV from '@/components/Templates/CV/DarkSidebar.vue';
import CircularPhotoCV from '@/components/Templates/CV/CircularPhoto.vue';
import InvoicePurple from '@/components/Templates/Invoice/Purple.vue';
import InvoiceOrangeBlack from '@/components/Templates/Invoice/OrangeBlack.vue';
import InvoiceRedWave from '@/components/Templates/Invoice/RedWave.vue';
import InvoiceBlueClean from '@/components/Templates/Invoice/BlueClean.vue';
import CoverLetterRoundedGreen from '@/components/Templates/CoverLetter/RoundedGreen.vue';
import CoverLetterOrganicOrange from '@/components/Templates/CoverLetter/OrganicOrange.vue';
import CertificateGoldBlack from '@/components/Templates/Certificate/GoldBlack.vue';
import CertificateBlueGold from '@/components/Templates/Certificate/BlueGold.vue';
import CvScore from '@/components/CvScore.vue';
import AtsAnalysis from '@/components/AtsAnalysis.vue';
import ShareBox from '@/components/ShareBox.vue';
import { useCvScore } from '@/composables/useCvScore';
import { Download, Save, Check, Loader2, AlertTriangle, Sparkles, Plus, Trash2, Upload, FileText } from '@lucide/vue';

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
        education?: unknown[];
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
    initialType?: string | null;
    initialTemplate?: string | null;
}>();

const store = useDocumentStore();
const activeTab = ref<'edit' | 'preview'>('edit');
const isSaving = ref(false);
const isUploadingPhoto = ref(false);
const isImportingCv = ref(false);
const isExportingGuest = ref(false);

// Parcours invité : on peut créer/prévisualiser/exporter sans compte. La
// sauvegarde réelle (autosave, historique, corbeille...) nécessite un compte.
const GUEST_DRAFT_KEY = 'innovapdf_pending_document';
const isGuest = computed(() => !usePage().props.auth?.user);

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
            if (props.initialType) {
                store.currentDocument.type = props.initialType as never;
            }
            if (props.initialTemplate) {
                store.currentDocument.template = props.initialTemplate;
            }
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
            education: (doc.content?.education as never) ?? [],
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
        education: store.currentDocument.education,
        invoiceItems: store.currentDocument.invoiceItems,
        invoiceMeta: store.currentDocument.invoiceMeta,
        coverLetter: store.currentDocument.coverLetter,
        certificate: store.currentDocument.certificate,
    },
    style: store.currentDocument.style,
});

const save = () => {
    if (autosaveTimer) { clearTimeout(autosaveTimer); autosaveTimer = null; }

    // Invité (pas encore de compte) : on ne peut rien persister côté serveur.
    // On garde le brouillon en sessionStorage et on l'emmène s'inscrire ;
    // le Dashboard se chargera de créer le document une fois connecté.
    if (isGuest.value) {
        sessionStorage.setItem(GUEST_DRAFT_KEY, JSON.stringify(buildPayload()));
        router.visit('/register?intent=save_document');
        return;
    }

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
        if (isGuest.value) return; // pas d'autosave possible sans compte

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
        education: store.currentDocument.education,
            },
        });
        store.currentDocument.profile!.summary = data.result;
    } catch (e: any) {
        saveError.value = e.response?.data?.message ?? "L'assistant IA n'est pas disponible pour le moment.";
    } finally {
        isGeneratingSummary.value = false;
    }
};

const exportPdf = async () => {
    if (isGuest.value) {
        isExportingGuest.value = true;
        saveError.value = null;

        try {
            const response = await axios.post('/guest/export', buildPayload(), { responseType: 'blob' });
            const url = window.URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }));
            const link = document.createElement('a');
            link.href = url;
            link.download = `${store.currentDocument.title || 'document'}.pdf`;
            link.click();
            window.URL.revokeObjectURL(url);
        } catch (e) {
            saveError.value = "Impossible de générer le PDF. Vérifie que tous les champs requis sont remplis.";
        } finally {
            isExportingGuest.value = false;
        }
        return;
    }

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
    <div class="min-h-screen bg-[var(--innova-bg)] text-[var(--innova-text)] flex flex-col">
        <!-- En-tête avec Sauvegarde -->
        <header class="bg-[var(--innova-surface)] border-b border-[var(--innova-border)] px-6 py-4 flex justify-between items-center">
            <input
                v-model="store.currentDocument.title"
                type="text"
                class="text-xl font-bold border-none bg-transparent text-[var(--innova-text)] placeholder:text-[var(--innova-text-muted)] focus:ring-0 focus:outline-none"
                placeholder="Titre du document..."
            />

            <div class="flex items-center space-x-3">
                <!-- Bascule mobile -->
                <div class="md:hidden flex space-x-1">
                    <button
                        @click="activeTab = 'edit'"
                        :class="activeTab === 'edit' ? 'bg-[var(--innova-green)] text-black' : 'bg-[var(--innova-surface-2)] text-[var(--innova-text-muted)]'"
                        class="px-3 py-1.5 rounded-full text-sm font-medium transition"
                    >
                        Éditer
                    </button>
                    <button
                        @click="activeTab = 'preview'"
                        :class="activeTab === 'preview' ? 'bg-[var(--innova-green)] text-black' : 'bg-[var(--innova-surface-2)] text-[var(--innova-text-muted)]'"
                        class="px-3 py-1.5 rounded-full text-sm font-medium transition"
                    >
                        Aperçu
                    </button>
                </div>

                <button
                    v-if="documentId || isGuest"
                    @click="exportPdf"
                    :disabled="isExportingGuest"
                    class="inline-flex items-center gap-2 bg-[var(--innova-surface-2)] border border-[var(--innova-border)] hover:border-[var(--innova-green)]/50 hover:brightness-110 text-[var(--innova-text)] px-4 py-2.5 rounded-full font-medium text-sm transition disabled:opacity-50"
                >
                    <Loader2 v-if="isExportingGuest" class="size-4 animate-spin" />
                    <Download v-else class="size-4" />
                    {{ isExportingGuest ? 'Génération…' : 'Exporter en PDF' }}
                </button>

                <span v-if="!isGuest" class="text-xs text-[var(--innova-text-muted)] inline-flex items-center gap-1.5 min-w-[140px]">
                    <span v-if="saveStatus === 'saving'" class="inline-flex items-center gap-1.5"><Loader2 class="size-3.5 animate-spin" /> Enregistrement…</span>
                    <span v-else-if="saveStatus === 'saved'" class="inline-flex items-center gap-1.5 text-[var(--innova-green-light)]"><Check class="size-3.5" /> Enregistré</span>
                    <span v-else-if="saveStatus === 'unsaved'">Modifications non enregistrées</span>
                    <span v-else-if="saveStatus === 'error'" class="inline-flex items-center gap-1.5 text-red-400"><AlertTriangle class="size-3.5" /> Échec de l'enregistrement</span>
                </span>

                <button
                    @click="save"
                    :disabled="isSaving"
                    class="inline-flex items-center gap-2 bg-[var(--innova-green)] hover:brightness-110 text-black px-5 py-2.5 rounded-full font-semibold text-sm transition disabled:opacity-50 shadow-[0_0_24px_-4px_rgba(34,197,94,0.6)]"
                >
                    <Loader2 v-if="isSaving" class="size-4 animate-spin" />
                    <Save v-else class="size-4" />
                    {{ isGuest ? 'Créer mon compte pour enregistrer' : (isSaving ? 'Enregistrement...' : 'Sauvegarder') }}
                </button>
            </div>
        </header>

        <p v-if="saveError" class="bg-red-950/40 text-red-300 text-sm px-6 py-2 border-b border-red-900/40">
            {{ saveError }}
        </p>
        <p v-if="saveSuccess" class="bg-[var(--innova-surface-2)] text-[var(--innova-green-light)] text-sm px-6 py-2 border-b border-[var(--innova-border)]">
            {{ saveSuccess }}
        </p>

        <!-- Corps Dual-Pane -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Formulaire (Panneau Gauche) -->
            <div :class="['w-full md:w-[36%] md:min-w-[380px] p-6 overflow-y-auto bg-[var(--innova-surface)] border-r border-[var(--innova-border)] space-y-6', activeTab === 'preview' ? 'hidden md:block' : 'block']">

                <!-- Sélection Type & Modèle -->
                <div class="border-b border-[var(--innova-border)] pb-4 space-y-3">
                    <h2 class="text-md font-semibold text-[var(--innova-text)]">Paramètres du Document</h2>
                    <div>
                        <label class="block text-sm font-medium text-[var(--innova-text-muted)]">Type de Document</label>
                        <select v-model="store.currentDocument.type" class="w-full text-sm rounded-md bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] mt-1 px-2 py-1.5">
                            <option value="cv">Curriculum Vitae (CV)</option>
                            <option value="cover_letter">Lettre de motivation</option>
                            <option value="invoice">Facture</option>
                            <option value="quote">Devis</option>
                            <option value="attestation">Attestation</option>
                            <option value="certificate">Certificat</option>
                        </select>
                    </div>

                    <div v-if="!documentId && store.currentDocument.type === 'cv' && !isGuest">
                        <label class="text-xs text-[var(--innova-green)] hover:underline font-medium cursor-pointer inline-flex items-center gap-1">
                            <input type="file" accept="application/pdf" class="hidden" @change="importCv" :disabled="isImportingCv" />
                            <Loader2 v-if="isImportingCv" class="size-3.5 animate-spin" />
                            <Upload v-else class="size-3.5" />
                            {{ isImportingCv ? 'Import en cours…' : 'Importer un CV existant (PDF)' }}
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[var(--innova-text-muted)]">Modèle (Template)</label>
                        <select v-model="store.currentDocument.template" class="w-full text-sm rounded-md bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] mt-1 px-2 py-1.5">
                            <option v-if="store.currentDocument.type === 'cv'" value="monochrome">CV Monochrome</option>
                            <option v-if="store.currentDocument.type === 'cv'" value="photo-banner">CV Bandeau Photo</option>
                            <option v-if="store.currentDocument.type === 'cv'" value="dark-sidebar">CV Sidebar Sombre</option>
                            <option v-if="store.currentDocument.type === 'cv'" value="circular-photo">CV Photo Circulaire</option>
                            <option v-if="store.currentDocument.type === 'cover_letter'" value="rounded-green">Lettre Verte Arrondie</option>
                            <option v-if="store.currentDocument.type === 'cover_letter'" value="organic-orange">Lettre Orange Organique</option>
                            <option v-if="['invoice', 'quote'].includes(store.currentDocument.type)" value="purple">Violet</option>
                            <option v-if="['invoice', 'quote'].includes(store.currentDocument.type)" value="orange-black">Orange & Noir</option>
                            <option v-if="['invoice', 'quote'].includes(store.currentDocument.type)" value="red-wave">Rouge Vague</option>
                            <option v-if="['invoice', 'quote'].includes(store.currentDocument.type)" value="blue-clean">Bleu Épuré</option>
                            <option v-if="['attestation', 'certificate'].includes(store.currentDocument.type)" value="gold-black">Noir & Or</option>
                            <option v-if="['attestation', 'certificate'].includes(store.currentDocument.type)" value="blue-gold">Bleu & Or</option>
                        </select>
                    </div>
                </div>

                <!-- Informations Générales -->
                <div class="border-b border-[var(--innova-border)] pb-4 space-y-3">
                    <h2 class="text-md font-semibold text-[var(--innova-text)]">Informations Générales</h2>

                    <div v-if="store.currentDocument.type === 'cv'" class="flex items-center space-x-4">
                        <img
                            v-if="store.currentDocument.profile?.photoUrl"
                            :src="store.currentDocument.profile.photoUrl"
                            class="h-14 w-14 rounded-full object-cover border border-[var(--innova-border)]"
                            alt="Photo de profil"
                        />
                        <div v-if="!isGuest">
                            <label class="block text-xs font-medium text-[var(--innova-text-muted)] mb-1">Photo de profil</label>
                            <input type="file" accept="image/png,image/jpeg,image/webp" @change="onPhotoSelected" class="text-xs text-[var(--innova-text-muted)]" />
                            <p v-if="isUploadingPhoto" class="text-xs text-[var(--innova-text-muted)]">Envoi en cours...</p>
                        </div>
                        <p v-else class="text-xs text-[var(--innova-text-muted)]">
                            <a href="/register" class="text-[var(--innova-green)] hover:underline">Crée un compte</a> pour ajouter une photo.
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[var(--innova-text-muted)]">Nom complet</label>
                        <input v-if="store.currentDocument.profile" v-model="store.currentDocument.profile.fullName" type="text" class="mt-1 w-full text-sm rounded-md bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[var(--innova-text-muted)]">Titre / Intitulé</label>
                        <input v-if="store.currentDocument.profile" v-model="store.currentDocument.profile.title" type="text" class="mt-1 w-full text-sm rounded-md bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5" />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium text-[var(--innova-text-muted)]">Email</label>
                            <input v-if="store.currentDocument.profile" v-model="store.currentDocument.profile.email" type="email" class="mt-1 w-full text-sm rounded-md bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[var(--innova-text-muted)]">Téléphone</label>
                            <input v-if="store.currentDocument.profile" v-model="store.currentDocument.profile.phone" type="text" class="mt-1 w-full text-sm rounded-md bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5" />
                        </div>
                    </div>
                    <div class="mt-3" v-if="store.currentDocument.type === 'cv'">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-[var(--innova-text-muted)]">Résumé</label>
                            <div v-if="!isGuest" class="flex items-center gap-2">
                                <button @click="generateSummary" :disabled="isGeneratingSummary" type="button" class="inline-flex items-center gap-1 text-xs text-[var(--innova-green)] hover:underline font-medium disabled:opacity-50">
                                    <Loader2 v-if="isGeneratingSummary" class="size-3.5 animate-spin" />
                                    <Sparkles v-else class="size-3.5" />
                                    {{ isGeneratingSummary ? 'Génération…' : "Générer avec l'IA" }}
                                </button>
                                <AiAssistant v-model="store.currentDocument.profile!.summary" />
                            </div>
                        </div>
                        <textarea v-model="store.currentDocument.profile!.summary" rows="3" placeholder="Résumé professionnel..." class="mt-1 w-full text-sm rounded-md bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5"></textarea>
                        <p v-if="isGuest" class="text-xs text-[var(--innova-text-muted)] mt-1">
                            <a href="/register" class="text-[var(--innova-green)] hover:underline">Crée un compte</a> pour utiliser l'assistant IA.
                        </p>
                    </div>
                </div>

                <!-- Options de Style -->
                <div class="border-b border-[var(--innova-border)] pb-4">
                    <h2 class="text-md font-semibold text-[var(--innova-text)] mb-3">Style & Couleurs</h2>
                    <div class="flex items-center space-x-4">
                        <label class="text-sm text-[var(--innova-text-muted)]">Couleur principale :</label>
                        <input type="color" v-model="store.currentDocument.style.primaryColor" class="h-8 w-14 rounded-md border border-[var(--innova-border)] cursor-pointer bg-transparent" />
                    </div>
                </div>

                <!-- Section Expériences (si Type === 'cv') -->
                <div v-if="store.currentDocument.type === 'cv'" class="space-y-4">
                    <div class="flex justify-between items-center">
                        <h2 class="text-md font-semibold text-[var(--innova-text)]">Expériences</h2>
                        <button @click="store.addEmptyExperience" class="inline-flex items-center gap-1 text-xs bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-green)] px-3 py-1.5 rounded-full font-medium hover:brightness-110">
                            <Plus class="size-3.5" /> Ajouter
                        </button>
                    </div>

                    <div v-for="exp in store.currentDocument.experiences" :key="exp.id" class="p-4 border border-[var(--innova-border)] rounded-md bg-[var(--innova-surface-2)] space-y-3 relative">
                        <button @click="store.removeExperience(exp.id)" class="absolute top-2 right-2 text-red-400 text-xs inline-flex items-center gap-1 hover:text-red-300">
                            <Trash2 class="size-3.5" /> Supprimer
                        </button>
                        <input v-model="exp.position" placeholder="Poste" class="w-full text-sm rounded-md bg-[var(--innova-surface)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5" />
                        <input v-model="exp.company" placeholder="Entreprise" class="w-full text-sm rounded-md bg-[var(--innova-surface)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5" />
                        <div class="grid grid-cols-2 gap-2">
                            <input v-model="exp.startDate" placeholder="Début (ex: Jan 2022)" class="text-sm rounded-md bg-[var(--innova-surface)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5" />
                            <input v-model="exp.endDate" placeholder="Fin (ex: Présent)" class="text-sm rounded-md bg-[var(--innova-surface)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5" />
                        </div>
                        <textarea v-model="exp.description" placeholder="Missions..." class="w-full text-sm rounded-md bg-[var(--innova-surface)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5 rows-2"></textarea>
                        <AiAssistant v-if="!isGuest" v-model="exp.description" />
                    </div>
                </div>

                <!-- Section Éducation (si Type === 'cv') -->
                <div v-if="store.currentDocument.type === 'cv'" class="space-y-4 border-b border-[var(--innova-border)] pb-4">
                    <div class="flex justify-between items-center">
                        <h2 class="text-md font-semibold text-[var(--innova-text)]">Éducation</h2>
                        <button @click="store.addEmptyEducation" class="inline-flex items-center gap-1 text-xs bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-green)] px-3 py-1.5 rounded-full font-medium hover:brightness-110">
                            <Plus class="size-3.5" /> Ajouter
                        </button>
                    </div>
                    <div v-for="edu in store.currentDocument.education" :key="edu.id" class="p-3 border border-[var(--innova-border)] rounded-md bg-[var(--innova-surface-2)] space-y-2 relative">
                        <button @click="store.removeEducation(edu.id)" class="absolute top-2 right-2 text-red-400 text-xs inline-flex items-center gap-1 hover:text-red-300">
                            <Trash2 class="size-3.5" /> Supprimer
                        </button>
                        <input v-model="edu.degree" placeholder="Diplôme" class="w-full text-sm rounded-md bg-[var(--innova-surface)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5" />
                        <input v-model="edu.school" placeholder="École / Université" class="w-full text-sm rounded-md bg-[var(--innova-surface)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5" />
                        <div class="grid grid-cols-2 gap-2">
                            <input v-model="edu.startDate" placeholder="Début (ex: 2020)" class="text-sm rounded-md bg-[var(--innova-surface)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5" />
                            <input v-model="edu.endDate" placeholder="Fin (ex: 2023)" class="text-sm rounded-md bg-[var(--innova-surface)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5" />
                        </div>
                    </div>
                </div>

                <!-- Section Compétences (si Type === 'cv') -->
                <div v-if="store.currentDocument.type === 'cv' && store.currentDocument.profile" class="border-b border-[var(--innova-border)] pb-4">
                    <h2 class="text-md font-semibold text-[var(--innova-text)] mb-2">Compétences</h2>
                    <input
                        :value="(store.currentDocument.profile.skills || []).join(', ')"
                        @input="store.currentDocument.profile!.skills = ($event.target as HTMLInputElement).value.split(',').map(s => s.trim()).filter(Boolean)"
                        placeholder="Ex: Gestion de projet, Excel, Communication"
                        class="w-full text-sm rounded-md bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5"
                    />
                    <p class="text-xs text-[var(--innova-text-muted)] mt-1">Sépare chaque compétence par une virgule.</p>
                </div>

                <div v-if="store.currentDocument.type === 'cv'" class="space-y-4">
                    <CvScore :score="cvScore" :issues="cvIssues" />
                    <AtsAnalysis v-if="!isGuest" :doc="store.currentDocument" />
                </div>

                <ShareBox v-if="documentId" :document-id="documentId" :share-url="props.shareUrl ?? null" />

                <!-- Section Attestation / Certificat -->
                <div v-if="['attestation', 'certificate'].includes(store.currentDocument.type) && store.currentDocument.certificate" class="border-b border-[var(--innova-border)] pb-4 space-y-3">
                    <h2 class="text-md font-semibold text-[var(--innova-text)]">{{ store.currentDocument.type === 'certificate' ? 'Certificat' : 'Attestation' }}</h2>
                    <div>
                        <label class="block text-sm font-medium text-[var(--innova-text-muted)]">Nom du bénéficiaire</label>
                        <input v-model="store.currentDocument.certificate.recipientName" type="text" class="mt-1 w-full text-sm rounded-md bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[var(--innova-text-muted)]">Objet</label>
                        <input v-model="store.currentDocument.certificate.purpose" type="text" placeholder="Attestation de travail, de stage, de formation..." class="mt-1 w-full text-sm rounded-md bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5" />
                    </div>
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-[var(--innova-text-muted)]">Texte</label>
                            <AiAssistant v-if="!isGuest" v-model="store.currentDocument.certificate.body" />
                        </div>
                        <textarea v-model="store.currentDocument.certificate.body" rows="6" placeholder="Je soussigné(e)..." class="mt-1 w-full text-sm rounded-md bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-[var(--innova-text-muted)]">Date d'émission</label>
                            <input v-model="store.currentDocument.certificate.issueDate" type="date" class="mt-1 w-full text-sm rounded-md bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[var(--innova-text-muted)]">Signataire</label>
                            <input v-model="store.currentDocument.certificate.issuerName" type="text" class="mt-1 w-full text-sm rounded-md bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[var(--innova-text-muted)]">Fonction du signataire</label>
                        <input v-model="store.currentDocument.certificate.issuerTitle" type="text" placeholder="Directeur RH, Responsable formation..." class="mt-1 w-full text-sm rounded-md bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5" />
                    </div>
                </div>

                <!-- Section Lettre de motivation -->
                <div v-if="store.currentDocument.type === 'cover_letter' && store.currentDocument.coverLetter" class="border-b border-[var(--innova-border)] pb-4 space-y-3">
                    <h2 class="text-md font-semibold text-[var(--innova-text)]">Lettre de motivation</h2>
                    <div>
                        <label class="block text-sm font-medium text-[var(--innova-text-muted)]">Destinataire</label>
                        <input v-model="store.currentDocument.coverLetter.recipientName" type="text" placeholder="Nom du recruteur" class="mt-1 w-full text-sm rounded-md bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[var(--innova-text-muted)]">Entreprise</label>
                        <input v-model="store.currentDocument.coverLetter.recipientCompany" type="text" class="mt-1 w-full text-sm rounded-md bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[var(--innova-text-muted)]">Objet</label>
                        <input v-model="store.currentDocument.coverLetter.subject" type="text" placeholder="Candidature au poste de..." class="mt-1 w-full text-sm rounded-md bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5" />
                    </div>
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-[var(--innova-text-muted)]">Corps de la lettre</label>
                            <AiAssistant v-if="!isGuest" v-model="store.currentDocument.coverLetter.body" />
                        </div>
                        <textarea v-model="store.currentDocument.coverLetter.body" rows="10" placeholder="Madame, Monsieur,..." class="mt-1 w-full text-sm rounded-md bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5"></textarea>
                    </div>
                </div>

                <!-- Section Articles (si Type === 'invoice' ou 'quote') -->
                <div v-if="['invoice', 'quote'].includes(store.currentDocument.type)" class="space-y-4">
                    <div class="flex justify-between items-center">
                        <h2 class="text-md font-semibold text-[var(--innova-text)]">Articles / Prestations</h2>
                        <button @click="store.addEmptyInvoiceItem" class="inline-flex items-center gap-1 text-xs bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-green)] px-3 py-1.5 rounded-full font-medium hover:brightness-110">
                            <Plus class="size-3.5" /> Ajouter une ligne
                        </button>
                    </div>

                    <div v-if="store.currentDocument.invoiceMeta" class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-[var(--innova-text-muted)]">TVA (%)</label>
                            <input
                                :value="store.currentDocument.invoiceMeta.taxRate * 100"
                                @input="store.currentDocument.invoiceMeta!.taxRate = Number(($event.target as HTMLInputElement).value) / 100"
                                type="number" min="0" max="100" step="0.5"
                                class="w-full text-sm rounded-md bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-[var(--innova-text-muted)]">Devise</label>
                            <select v-model="store.currentDocument.invoiceMeta.currency" class="w-full text-sm rounded-md bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5">
                                <option value="FCFA">FCFA</option>
                                <option value="EUR">EUR</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                    </div>

                    <div v-for="item in store.currentDocument.invoiceItems" :key="item.id" class="p-3 border border-[var(--innova-border)] rounded-md bg-[var(--innova-surface-2)] space-y-2 relative">
                        <button @click="store.removeInvoiceItem(item.id)" class="absolute top-2 right-2 text-red-400 text-xs inline-flex items-center gap-1 hover:text-red-300">
                            <Trash2 class="size-3.5" /> Supprimer
                        </button>
                        <input v-model="item.description" placeholder="Description de l'article" class="w-full text-sm rounded-md bg-[var(--innova-surface)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5" />
                        <div class="grid grid-cols-2 gap-2">
                            <input v-model.number="item.quantity" type="number" placeholder="Quantité" class="text-sm rounded-md bg-[var(--innova-surface)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5" />
                            <input v-model.number="item.unitPrice" type="number" placeholder="Prix Unitaire (FCFA)" class="text-sm rounded-md bg-[var(--innova-surface)] border border-[var(--innova-border)] text-[var(--innova-text)] focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] px-2 py-1.5" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Prévisualisation A4 (Panneau Droit) -->
            <div :class="['w-full md:w-[64%] p-6 overflow-y-auto bg-[var(--innova-bg)] flex justify-center items-start', activeTab === 'edit' ? 'hidden md:flex' : 'flex']">
                <div :class="['bg-white shadow-2xl p-8 max-w-full', ['attestation', 'certificate'].includes(store.currentDocument.type) ? 'aspect-[1.414/1] w-[297mm] min-h-[210mm]' : 'aspect-[1/1.414] w-[210mm] min-h-[297mm]']">
                    <MonochromeCV v-if="store.currentDocument.type === 'cv' && store.currentDocument.template === 'monochrome'" :doc="store.currentDocument" />
                    <BannerPhotoCV v-if="store.currentDocument.type === 'cv' && store.currentDocument.template === 'photo-banner'" :doc="store.currentDocument" />
                    <DarkSidebarCV v-if="store.currentDocument.type === 'cv' && store.currentDocument.template === 'dark-sidebar'" :doc="store.currentDocument" />
                    <CircularPhotoCV v-if="store.currentDocument.type === 'cv' && store.currentDocument.template === 'circular-photo'" :doc="store.currentDocument" />
                    <InvoicePurple v-if="['invoice','quote'].includes(store.currentDocument.type) && store.currentDocument.template === 'purple'" :doc="store.currentDocument" />
                    <InvoiceOrangeBlack v-if="['invoice','quote'].includes(store.currentDocument.type) && store.currentDocument.template === 'orange-black'" :doc="store.currentDocument" />
                    <InvoiceRedWave v-if="['invoice','quote'].includes(store.currentDocument.type) && store.currentDocument.template === 'red-wave'" :doc="store.currentDocument" />
                    <InvoiceBlueClean v-if="['invoice','quote'].includes(store.currentDocument.type) && store.currentDocument.template === 'blue-clean'" :doc="store.currentDocument" />
                    <CoverLetterRoundedGreen v-if="store.currentDocument.type === 'cover_letter' && store.currentDocument.template === 'rounded-green'" :doc="store.currentDocument" />
                    <CoverLetterOrganicOrange v-if="store.currentDocument.type === 'cover_letter' && store.currentDocument.template === 'organic-orange'" :doc="store.currentDocument" />
                    <CertificateGoldBlack v-if="['attestation', 'certificate'].includes(store.currentDocument.type) && store.currentDocument.template === 'gold-black'" :doc="store.currentDocument" />
                    <CertificateBlueGold v-if="['attestation', 'certificate'].includes(store.currentDocument.type) && store.currentDocument.template === 'blue-gold'" :doc="store.currentDocument" />
                </div>
            </div>
        </div>
    </div>
</template>