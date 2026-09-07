<script setup lang="ts">
import { computed } from 'vue';
import type { DocumentSchema } from '@/types/document';

const props = defineProps<{
    doc: DocumentSchema;
}>();

const subtotal = computed(() => {
    return (props.doc.invoiceItems || []).reduce((sum, item) => sum + (item.quantity * item.unitPrice), 0);
});

const taxRate = 0.18; // TVA 18%
const taxAmount = computed(() => subtotal.value * taxRate);
const total = computed(() => subtotal.value + taxAmount.value);
</script>

<template>
    <div class="h-full w-full bg-white p-8 text-gray-800 flex flex-col justify-between" :style="{ fontFamily: doc.style.fontFamily }">
        <div>
            <!-- En-tête Facture -->
            <div class="flex justify-between items-start mb-10 border-b pb-6">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight" :style="{ color: doc.style.primaryColor }">
                        FACTURE
                    </h1>
                    <p class="text-xs text-gray-500 mt-1">Réf : {{ doc.title || 'FACT-2026-001' }}</p>
                </div>
                <div class="text-right text-xs text-gray-600 space-y-1">
                    <p class="font-bold text-gray-900 text-sm">{{ doc.profile?.fullName || 'Votre Nom / Entreprise' }}</p>
                    <p v-if="doc.profile?.email">{{ doc.profile.email }}</p>
                    <p v-if="doc.profile?.phone">{{ doc.profile.phone }}</p>
                    <p v-if="doc.profile?.location">{{ doc.profile.location }}</p>
                </div>
            </div>

            <!-- Tableau des Articles -->
            <table class="w-full text-left text-xs mb-8 border-collapse">
                <thead>
                    <tr class="border-b-2 text-gray-700" :style="{ borderColor: doc.style.primaryColor }">
                        <th class="py-2 font-bold">Désignation</th>
                        <th class="py-2 text-center font-bold">Quantité</th>
                        <th class="py-2 text-right font-bold">Prix Unitaire</th>
                        <th class="py-2 text-right font-bold">Total HT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="item in doc.invoiceItems" :key="item.id">
                        <td class="py-3 font-medium text-gray-900">{{ item.description || 'Description de l\'article' }}</td>
                        <td class="py-3 text-center text-gray-600">{{ item.quantity }}</td>
                        <td class="py-3 text-right text-gray-600">{{ item.unitPrice.toLocaleString('fr-FR') }} FCFA</td>
                        <td class="py-3 text-right font-semibold text-gray-900">
                            {{ (item.quantity * item.unitPrice).toLocaleString('fr-FR') }} FCFA
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Total et Pied de Page -->
        <div>
            <div class="flex justify-end mb-8">
                <div class="w-1/2 space-y-2 text-xs border-t pt-4">
                    <div class="flex justify-between text-gray-600">
                        <span>Sous-total HT :</span>
                        <span class="font-medium text-gray-900">{{ subtotal.toLocaleString('fr-FR') }} FCFA</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>TVA (18%) :</span>
                        <span class="font-medium text-gray-900">{{ taxAmount.toLocaleString('fr-FR') }} FCFA</span>
                    </div>
                    <div class="flex justify-between text-sm font-bold pt-2 border-t" :style="{ color: doc.style.primaryColor }">
                        <span>Total TTC :</span>
                        <span>{{ total.toLocaleString('fr-FR') }} FCFA</span>
                    </div>
                </div>
            </div>

            <div class="text-center text-[10px] text-gray-400 border-t pt-4">
                Merci pour votre confiance. Facture générée via INNOVAPDF.
            </div>
        </div>
    </div>
</template>