<script setup lang="ts">
import { computed } from 'vue';
import type { DocumentSchema } from '@/types/document';

const props = defineProps<{ doc: DocumentSchema }>();
const label = computed(() => props.doc.type === 'quote' ? 'Devis' : 'Invoice/Bile');
const subtotal = computed(() => (props.doc.invoiceItems || []).reduce((s, i) => s + i.quantity * i.unitPrice, 0));
const taxRate = computed(() => props.doc.invoiceMeta?.taxRate ?? 0.18);
const currency = computed(() => props.doc.invoiceMeta?.currency ?? 'FCFA');
const total = computed(() => subtotal.value * (1 + taxRate.value));
</script>

<template>
    <div class="h-full w-full bg-white text-gray-800 p-10 flex flex-col" :style="{ fontFamily: doc.style.fontFamily }">
        <div class="flex justify-between items-start mb-8">
            <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center text-white text-xs font-bold" :style="{ backgroundColor: doc.style.primaryColor }">IP</div>
                <div>
                    <p class="font-bold text-sm">{{ doc.profile?.fullName || 'BRANDTEXT' }}</p>
                    <p class="text-[9px] text-gray-400 tracking-wide">TAGLINE SPACE</p>
                </div>
            </div>
            <p class="text-xl font-bold">{{ label }}</p>
        </div>

        <div class="text-[10px] text-gray-500 grid grid-cols-2 gap-4 mb-6">
            <p>Réf : {{ doc.title || 'DOC-001' }}</p>
            <p class="text-right">Date : {{ new Date().toLocaleDateString('fr-FR') }}</p>
        </div>

        <table class="w-full text-xs mb-6">
            <thead>
                <tr class="text-white" :style="{ backgroundColor: doc.style.primaryColor }">
                    <th class="text-left py-2 px-2">N°</th>
                    <th class="text-left py-2 px-2">Description</th>
                    <th class="text-center py-2 px-2">Qté</th>
                    <th class="text-right py-2 px-2">P.U.</th>
                    <th class="text-right py-2 px-2">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(item, i) in doc.invoiceItems" :key="item.id" class="border-b border-gray-100">
                    <td class="py-2 px-2 text-gray-400">{{ String(i + 1).padStart(2, '0') }}</td>
                    <td class="py-2 px-2">{{ item.description || "Description de l'article" }}</td>
                    <td class="py-2 px-2 text-center">{{ item.quantity }}</td>
                    <td class="py-2 px-2 text-right">{{ item.unitPrice.toLocaleString('fr-FR') }}</td>
                    <td class="py-2 px-2 text-right font-semibold">{{ (item.quantity * item.unitPrice).toLocaleString('fr-FR') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="flex justify-end mb-auto">
            <p class="font-bold text-sm">Total : {{ total.toLocaleString('fr-FR') }} {{ currency }}</p>
        </div>

        <div class="relative mt-6 -mx-10 -mb-10 pt-6">
            <div class="h-6" :style="{ backgroundColor: doc.style.primaryColor, borderRadius: '50% 50% 0 0 / 100% 100% 0 0' }"></div>
            <div class="bg-[#1c1c1c] text-white text-[9px] flex justify-center gap-6 py-3">
                <span v-if="doc.profile?.phone">{{ doc.profile.phone }}</span>
                <span v-if="doc.profile?.email">{{ doc.profile.email }}</span>
            </div>
        </div>
    </div>
</template>
