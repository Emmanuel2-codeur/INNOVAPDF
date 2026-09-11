<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { useDocumentStore } from '@/stores/document';
import MinimalCV from '@/components/Templates/CV/Minimal.vue';
import ModernCV from '@/components/Templates/CV/Modern.vue';
import ClassicInvoice from '@/components/Templates/Invoice/Classic.vue';

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
    };
    style: Record<string, unknown>;
}

const props = defineProps<{
    document: BackendDocument | null;
}>();

const store = useDocumentStore();
const activeTab = ref<'edit' | 'preview'>('edit');
const isSaving = ref(false);
const isUploadingPhoto = ref(false);
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

const exportPdf = () => {
    if (!documentId.value) return;
    window.open(`/documents/${documentId.value}/export`, '_blank');
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
                            <option value="invoice">Facture</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Modèle (Template)</label>
                        <select v-model="store.currentDocument.template" class="w-full text-sm rounded border-gray-300 mt-1">
                            <option v-if="store.currentDocument.type === 'cv'" value="minimal">CV Minimal</option>
                            <option v-if="store.currentDocument.type === 'cv'" value="modern">CV Modern</option>
                            <option v-if="store.currentDocument.type === 'invoice'" value="classic">Facture Classic</option>
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
                    </div>
                </div>

                <!-- Section Articles (si Type === 'invoice') -->
                <div v-if="store.currentDocument.type === 'invoice'" class="space-y-4">
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
                </div>
            </div>
        </div>
    </div>
</template>