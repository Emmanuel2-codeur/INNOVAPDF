<script setup lang="ts">
import { computed } from 'vue';
import type { DocumentSchema } from '@/types/document';

const props = defineProps<{ doc: DocumentSchema }>();
const label = computed(() => props.doc.type === 'quote' ? 'DEVIS' : 'INVOICE');
const subtotal = computed(() => (props.doc.invoiceItems || []).reduce((s, i) => s + i.quantity * i.unitPrice, 0));
const taxRate = computed(() => props.doc.invoiceMeta?.taxRate ?? 0.10);
const currency = computed(() => props.doc.invoiceMeta?.currency ?? 'FCFA');
const discount = computed(() => 0);
const taxAmount = computed(() => subtotal.value * taxRate.value);
const total = computed(() => subtotal.value + taxAmount.value - discount.value);
</script>

<template>
    <div class="h-full w-full bg-white text-gray-800 p-10" :style="{ fontFamily: doc.style.fontFamily }">
        <div class="flex justify-between items-start mb-8">
            <div class="flex items-center gap-2">
                <svg class="w-7 h-7" :style="{ color: doc.style.primaryColor }" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                <span class="font-bold">{{ doc.profile?.fullName || 'Company' }}</span>
            </div>
            <p class="text-2xl font-bold" :style="{ color: doc.style.primaryColor }">{{ label }}</p>
        </div>

        <div class="text-xs mb-6">
            <p class="font-bold">{{ doc.profile?.fullName || 'Client' }}</p>
            <p class="text-gray-500">{{ doc.profile?.phone }} · {{ doc.profile?.email }}</p>
        </div>

        <table class="w-full text-xs mb-6">
            <thead>
                <tr class="border-b-2" :style="{ borderColor: doc.style.primaryColor }">
                    <th class="text-left py-2">Description du produit</th>
                    <th class="text-right py-2">Prix</th>
                    <th class="text-right py-2">Qté</th>
                    <th class="text-right py-2">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in doc.invoiceItems" :key="item.id" class="border-b border-gray-100">
                    <td class="py-2.5">{{ item.description || "Description de l'article" }}</td>
                    <td class="py-2.5 text-right">{{ item.unitPrice.toLocaleString('fr-FR') }}</td>
                    <td class="py-2.5 text-right">{{ item.quantity }}</td>
                    <td class="py-2.5 text-right">{{ (item.quantity * item.unitPrice).toLocaleString('fr-FR') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="flex justify-end">
            <div class="w-56 text-xs space-y-1">
                <div class="flex justify-between text-gray-500"><span>Subtotal :</span><span>{{ subtotal.toLocaleString('fr-FR') }}</span></div>
                <div class="flex justify-between text-gray-500"><span>Tax {{ Math.round(taxRate*1000)/10 }}% :</span><span>{{ taxAmount.toLocaleString('fr-FR') }}</span></div>
                <div class="flex justify-between font-bold text-white px-2 py-1.5 rounded" :style="{ backgroundColor: doc.style.primaryColor }"><span>Total :</span><span>{{ total.toLocaleString('fr-FR') }} {{ currency }}</span></div>
            </div>
        </div>
    </div>
</template>
