<script setup lang="ts">
import { ref } from 'vue';
import { useDocumentStore } from '@/stores/document';

const store = useDocumentStore();
const activeTab = ref<'edit' | 'preview'>('edit');
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <!-- En-tête de l'éditeur -->
        <header class="bg-white border-b px-6 py-4 flex justify-between items-center">
            <input
                v-model="store.currentDocument.title"
                type="text"
                class="text-xl font-bold border-none focus:ring-0 focus:outline-none"
                placeholder="Titre du document..."
            />
            
            <!-- Bascule réactive sur mobile -->
            <div class="md:hidden flex space-x-2">
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
        </header>

        <!-- Zone principale Dual-Pane -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Panneau Gauche : Formulaire de Saisie -->
            <div 
                :class="['w-full md:w-1/2 p-6 overflow-y-auto bg-white border-r', activeTab === 'preview' ? 'hidden md:block' : 'block']"
            >
                <h2 class="text-lg font-semibold mb-4">Informations Générales</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom complet</label>
                        <input 
                            v-if="store.currentDocument.profile"
                            v-model="store.currentDocument.profile.fullName" 
                            type="text" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Titre professionnel</label>
                        <input 
                            v-if="store.currentDocument.profile"
                            v-model="store.currentDocument.profile.title" 
                            type="text" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                </div>
            </div>

            <!-- Panneau Droit : Previsualisation A4 Temps Réel -->
            <div 
                :class="['w-full md:w-1/2 p-6 overflow-y-auto bg-gray-200 flex justify-center items-start', activeTab === 'edit' ? 'hidden md:flex' : 'flex']"
            >
                <div 
                    class="bg-white shadow-2xl p-8 aspect-[1/1.414] w-[210mm] max-w-full min-h-[297mm] text-black"
                    :style="{ fontFamily: store.currentDocument.style.fontFamily }"
                >
                    <h1 class="text-3xl font-bold" :style="{ color: store.currentDocument.style.primaryColor }">
                        {{ store.currentDocument.profile?.fullName || 'Votre Nom' }}
                    </h1>
                    <p class="text-xl text-gray-600">
                        {{ store.currentDocument.profile?.title || 'Votre Intitulé' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Extrait à placer dans la colonne gauche de Editor.vue -->
<script setup lang="ts">
import MinimalCV from '@/Components/Templates/CV/Minimal.vue';
import ModernCV from '@/Components/Templates/CV/Modern.vue';
import ClassicInvoice from '@/Components/Templates/Invoice/Classic.vue';
import { useDocumentStore } from '@/stores/document';

const store = useDocumentStore();
</script>

<template>
    <!-- Choix du Type et Template -->
    <div class="border-b pb-4 space-y-3">
        <label class="block text-sm font-medium text-gray-700">Type de Document</label>
        <select v-model="store.currentDocument.type" class="w-full text-sm rounded border-gray-300">
            <option value="cv">Curriculum Vitae (CV)</option>
            <option value="invoice">Facture</option>
        </select>

        <label class="block text-sm font-medium text-gray-700">Modèle (Template)</label>
        <select v-model="store.currentDocument.template" class="w-full text-sm rounded border-gray-300">
            <option v-if="store.currentDocument.type === 'cv'" value="minimal">CV Minimal</option>
            <option v-if="store.currentDocument.type === 'cv'" value="modern">CV Modern</option>
            <option v-if="store.currentDocument.type === 'invoice'" value="classic">Facture Classic</option>
        </select>
    </div>

    <!-- Section Formulaire Articles Facture (affiché uniquement si type === 'invoice') -->
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
</template>