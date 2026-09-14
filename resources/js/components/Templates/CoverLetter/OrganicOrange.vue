<script setup lang="ts">
import type { DocumentSchema } from '@/types/document';

defineProps<{ doc: DocumentSchema }>();
</script>

<template>
    <div class="h-full w-full relative overflow-hidden" style="background-color: #fde9c8;" :style="{ fontFamily: doc.style.fontFamily }">
        <div class="absolute top-0 right-0 w-64 h-64 rounded-full opacity-40 -mr-20 -mt-20" :style="{ backgroundColor: doc.style.primaryColor }"></div>
        <div class="absolute bottom-0 left-0 w-52 h-52 rounded-full opacity-30 -ml-16 -mb-16" :style="{ backgroundColor: doc.style.primaryColor }"></div>

        <div class="relative bg-white/90 m-8 rounded-3xl p-10 shadow-sm text-xs text-gray-700 leading-relaxed">
            <p>{{ doc.profile?.fullName || '[Prénom & Nom du candidat]' }}</p>
            <p>{{ doc.profile?.location || '[Adresse du candidat]' }}</p>
            <p v-if="doc.profile?.phone">{{ doc.profile.phone }}</p>
            <p v-if="doc.profile?.email">{{ doc.profile.email }}</p>

            <p class="text-right mt-4">À l'attention de<br><strong>{{ doc.coverLetter?.recipientName || '[Nom du recruteur]' }}</strong><br>{{ doc.coverLetter?.recipientCompany }}</p>

            <p class="mt-6">Objet : <strong>{{ doc.coverLetter?.subject || 'Candidature' }}</strong></p>

            <p class="mt-4">Madame, Monsieur,</p>
            <div class="mt-3 whitespace-pre-line text-justify">{{ doc.coverLetter?.body || '' }}</div>

            <p class="mt-6">{{ doc.profile?.fullName }}</p>
        </div>
    </div>
</template>
