<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useDocumentStore } from '@/stores/document';
import MinimalCV from '@/components/templates/cv/Minimal.vue';
import ModernCV from '@/components/templates/cv/Modern.vue';
import ClassicInvoice from '@/components/templates/invoice/Classic.vue';

const store = useDocumentStore();
const activeTab = ref<'edit' | 'preview'>('edit');
const isSaving = ref(false);

const save = () => {
    isSaving.value = true;
    router.post('/documents', {
        title: store.currentDocument.title,
        type: store.currentDocument.type,
        template: store.currentDocument.template,
        content: {
            profile: store.currentDocument.profile,
            experiences: store.currentDocument.experiences,
            invoiceItems: store.currentDocument.invoiceItems,
        },
        style: store.currentDocument.style,
    }, {
        onFinish: () => { isSaving.value = false; }
    });
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
                    @click="save"
                    :disabled="isSaving"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md font-medium text-sm transition disabled:opacity-50"
                >
                    {{ isSaving ? 'Enregistrement...' : 'Sauvegarder' }}
                </button>
            </div>
        </header>

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