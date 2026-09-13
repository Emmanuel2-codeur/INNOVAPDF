<script setup lang="ts">
import type { DocumentSchema } from '@/types/document';

defineProps<{
    doc: DocumentSchema;
}>();
</script>

<template>
    <div class="h-full w-full bg-white p-10 text-gray-800" :style="{ fontFamily: doc.style.fontFamily }">
        <div class="flex justify-between text-xs mb-10">
            <div>
                <p class="font-bold">{{ doc.profile?.fullName || 'Nom Prénom' }}</p>
                <p v-if="doc.profile?.email">{{ doc.profile.email }}</p>
                <p v-if="doc.profile?.phone">{{ doc.profile.phone }}</p>
            </div>
            <div class="text-right">
                <p class="font-bold">{{ doc.coverLetter?.recipientName || 'Destinataire' }}</p>
                <p>{{ doc.coverLetter?.recipientCompany }}</p>
            </div>
        </div>

        <p class="text-sm font-semibold mb-6" :style="{ color: doc.style.primaryColor }">
            Objet : {{ doc.coverLetter?.subject || "Candidature" }}
        </p>

        <div class="text-sm whitespace-pre-line leading-relaxed">
            {{ doc.coverLetter?.body || "Madame, Monsieur,\n\n..." }}
        </div>
    </div>
</template>
