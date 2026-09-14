<script setup lang="ts">
import { computed } from 'vue';
import type { DocumentSchema } from '@/types/document';

const props = defineProps<{ doc: DocumentSchema }>();
const label = computed(() => props.doc.type === 'quote' ? 'DEVIS' : 'INVOICE');
const subtotal = computed(() => (props.doc.invoiceItems || []).reduce((s, i) => s + i.quantity * i.unitPrice, 0));
const taxRate = computed(() => props.doc.invoiceMeta?.taxRate ?? 0.15);
const currency = computed(() => props.doc.invoiceMeta?.currency ?? 'FCFA');
const taxAmount = computed(() => subtotal.value * taxRate.value);
const total = computed(() => subtotal.value + taxAmount.value);
</script>

<template>
    <div class="h-full w-full bg-white text-gray-800 p-10" :style="{ fontFamily: doc.style.fontFamily }">
        <div class="flex justify-between items-start mb-10">
            <div>
                <div class="w-8 h-8 rounded-full border-4 mb-2" :style="{ borderColor: doc.style.primaryColor }"></div>
                <p class="font-bold" :style="{ color: doc.style.primaryColor }">{{ doc.profile?.fullName || 'Business Name' }}</p>
            </div>
            <div class="text-right">
                <p class="text-2xl font-extrabold tracking-wide" :style="{ color: doc.style.primaryColor }">{{ label }}</p>
                <p class="text-xs text-gray-400">{{ new Date().toLocaleDateString('fr-FR') }}</p>
            </div>
        </div>

        <table class="w-full text-xs mb-4">
            <thead>
                <tr class="text-white" :style="{ backgroundColor: doc.style.primaryColor }">
                    <th class="text-left py-2 px-3 font-semibold">Description</th>
                    <th class="text-right py-2 px-3 font-semibold">P.U.</th>
                    <th class="text-right py-2 px-3 font-semibold">Qté</th>
                    <th class="text-right py-2 px-3 font-semibold">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in doc.invoiceItems" :key="item.id" class="border-b border-gray-100">
                    <td class="py-2.5 px-3">{{ item.description || "Description de l'article" }}</td>
                    <td class="py-2.5 px-3 text-right">{{ item.unitPrice.toLocaleString('fr-FR') }} {{ currency }}</td>
                    <td class="py-2.5 px-3 text-right">{{ item.quantity }}</td>
                    <td class="py-2.5 px-3 text-right">{{ (item.quantity * item.unitPrice).toLocaleString('fr-FR') }} {{ currency }}</td>
                </tr>
            </tbody>
        </table>

        <div class="flex justify-end mb-8">
            <div class="w-56 space-y-1.5 text-xs">
                <div class="flex justify-between"><span class="uppercase font-semibold" :style="{ color: doc.style.primaryColor }">Sous-total :</span><span>{{ subtotal.toLocaleString('fr-FR') }} {{ currency }}</span></div>
                <div class="flex justify-between"><span class="uppercase font-semibold" :style="{ color: doc.style.primaryColor }">TVA {{ Math.round(taxRate*1000)/10 }}% :</span><span>{{ taxAmount.toLocaleString('fr-FR') }} {{ currency }}</span></div>
                <div class="flex justify-between rounded px-3 py-2 text-white font-bold text-sm mt-1" :style="{ backgroundColor: doc.style.primaryColor }">
                    <span>TOTAL DÛ :</span><span>{{ total.toLocaleString('fr-FR') }} {{ currency }}</span>
                </div>
            </div>
        </div>

        <div class="text-xs text-gray-400">
            <p class="font-semibold mb-1">Merci pour votre confiance.</p>
        </div>
    </div>
</template>
