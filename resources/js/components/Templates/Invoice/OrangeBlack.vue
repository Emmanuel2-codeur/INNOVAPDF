<script setup lang="ts">
import { computed } from 'vue';
import type { DocumentSchema } from '@/types/document';

const props = defineProps<{ doc: DocumentSchema }>();
const label = computed(() => props.doc.type === 'quote' ? 'DEVIS' : 'INVOICE');
const subtotal = computed(() => (props.doc.invoiceItems || []).reduce((s, i) => s + i.quantity * i.unitPrice, 0));
const taxRate = computed(() => props.doc.invoiceMeta?.taxRate ?? 0.07);
const currency = computed(() => props.doc.invoiceMeta?.currency ?? 'FCFA');
const taxAmount = computed(() => subtotal.value * taxRate.value);
const total = computed(() => subtotal.value + taxAmount.value);
</script>

<template>
    <div class="h-full w-full bg-white text-gray-800 flex flex-col" :style="{ fontFamily: doc.style.fontFamily }">
        <div class="relative bg-[#1c1c1c] text-white p-8 pb-14 overflow-hidden">
            <div class="absolute top-0 right-0 h-full w-2/5" :style="{ backgroundColor: doc.style.primaryColor, clipPath: 'polygon(30% 0, 100% 0, 100% 100%, 0 100%)' }"></div>
            <div class="relative flex justify-between items-start">
                <div>
                    <p class="font-bold text-lg">{{ doc.profile?.fullName || 'Company Name' }}</p>
                    <p class="text-xs opacity-70">Votre slogan ici</p>
                </div>
                <p class="text-3xl font-black tracking-wide relative z-10">{{ label }}</p>
            </div>
        </div>

        <div class="p-8 flex-1 flex flex-col justify-between -mt-6">
            <div>
                <div class="bg-white shadow rounded p-4 grid grid-cols-2 gap-4 text-xs mb-6">
                    <div>
                        <p class="font-bold uppercase text-[10px] mb-1" :style="{ color: doc.style.primaryColor }">Facturé à</p>
                        <p>{{ doc.profile?.email || '' }}</p>
                        <p>{{ doc.profile?.phone || '' }}</p>
                    </div>
                    <div class="text-right">
                        <p><strong>Réf :</strong> {{ doc.title || 'DOC-001' }}</p>
                        <p><strong>Date :</strong> {{ new Date().toLocaleDateString('fr-FR') }}</p>
                    </div>
                </div>

                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-[#1c1c1c] text-white">
                            <th class="text-left py-2 px-3">Description</th>
                            <th class="text-right py-2 px-3">Prix</th>
                            <th class="text-center py-2 px-3">Qté</th>
                            <th class="text-right py-2 px-3">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in doc.invoiceItems" :key="item.id" class="border-b border-gray-100">
                            <td class="py-2.5 px-3 font-medium">{{ item.description || "Description de l'article" }}</td>
                            <td class="py-2.5 px-3 text-right">{{ item.unitPrice.toLocaleString('fr-FR') }}</td>
                            <td class="py-2.5 px-3 text-center">{{ item.quantity }}</td>
                            <td class="py-2.5 px-3 text-right font-semibold">{{ (item.quantity * item.unitPrice).toLocaleString('fr-FR') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end">
                <table class="text-xs w-56">
                    <tr><td class="py-1 text-gray-500">Sous-total :</td><td class="py-1 text-right">{{ subtotal.toLocaleString('fr-FR') }}</td></tr>
                    <tr><td class="py-1 text-gray-500">TVA {{ Math.round(taxRate*1000)/10 }}% :</td><td class="py-1 text-right">{{ taxAmount.toLocaleString('fr-FR') }}</td></tr>
                    <tr class="font-bold text-sm"><td class="py-2">Total :</td><td class="py-2 text-right" :style="{ color: doc.style.primaryColor }">{{ total.toLocaleString('fr-FR') }} {{ currency }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</template>
