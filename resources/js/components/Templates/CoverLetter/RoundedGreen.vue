<script setup lang="ts">
import type { DocumentSchema } from '@/types/document';

defineProps<{ doc: DocumentSchema }>();
</script>

<template>
    <div class="h-full w-full bg-[#faf3ea] text-gray-800 relative overflow-hidden" :style="{ fontFamily: doc.style.fontFamily }">
        <div class="absolute -top-10 left-1/2 -translate-x-1/2 w-40 h-40 rounded-full opacity-90" :style="{ backgroundColor: doc.style.secondaryColor || '#a7c4a0' }"></div>
        <div class="absolute top-16 left-1/2 translate-x-6 w-10 h-10 rounded-full" :style="{ backgroundColor: doc.style.primaryColor }"></div>

        <div class="relative pt-24">
            <div class="text-white px-10 py-6 flex justify-between text-xs" :style="{ backgroundColor: doc.style.primaryColor }">
                <div>
                    <p class="opacity-70 mb-1">Expéditeur :</p>
                    <p class="font-bold">{{ doc.profile?.fullName || 'Nom Prénom' }}</p>
                    <p v-if="doc.profile?.email">{{ doc.profile.email }}</p>
                    <p v-if="doc.profile?.phone">{{ doc.profile.phone }}</p>
                </div>
                <div class="text-right">
                    <p class="opacity-70 mb-1">Destinataire :</p>
                    <p class="font-bold">{{ doc.coverLetter?.recipientName || 'Destinataire' }}</p>
                    <p>{{ doc.coverLetter?.recipientCompany }}</p>
                </div>
            </div>

            <div class="p-10">
                <h1 class="text-2xl font-extrabold uppercase tracking-wide mb-4">Lettre de motivation</h1>
                <span class="inline-block text-white text-xs font-medium px-3 py-1 rounded mb-5" :style="{ backgroundColor: '#1f2937' }">
                    {{ doc.coverLetter?.subject || 'Candidature' }}
                </span>
                <p class="text-sm mb-4">Madame, Monsieur,</p>
                <div class="text-sm whitespace-pre-line leading-relaxed text-justify">{{ doc.coverLetter?.body || '' }}</div>
            </div>
        </div>
    </div>
</template>
