<!-- resources/js/Components/Templates/CV/Minimal.vue -->
<script setup lang="ts">
import type { DocumentSchema } from '@/types/document';

defineProps<{
    doc: DocumentSchema;
}>();
</script>

<template>
    <div class="h-full w-full bg-white text-gray-800" :style="{ fontFamily: doc.style.fontFamily }">
        <!-- En-tête -->
        <header class="border-b-2 pb-4 mb-6" :style="{ borderColor: doc.style.primaryColor }">
            <h1 class="text-3xl font-bold uppercase tracking-wide" :style="{ color: doc.style.primaryColor }">
                {{ doc.profile?.fullName || 'Nom Prénom' }}
            </h1>
            <p class="text-lg font-medium text-gray-600">{{ doc.profile?.title || 'Titre du poste' }}</p>
            <div class="mt-2 flex flex-wrap gap-4 text-xs text-gray-500">
                <span v-if="doc.profile?.email">📧 {{ doc.profile.email }}</span>
                <span v-if="doc.profile?.phone">📞 {{ doc.profile.phone }}</span>
                <span v-if="doc.profile?.location">📍 {{ doc.profile.location }}</span>
            </div>
        </header>

        <!-- Expériences -->
        <section v-if="doc.experiences && doc.experiences.length" class="mb-6">
            <h2 class="text-sm font-bold uppercase tracking-wider mb-3" :style="{ color: doc.style.primaryColor }">
                Expériences Professionnelles
            </h2>
            <div class="space-y-4">
                <div v-for="exp in doc.experiences" :key="exp.id" class="border-l-2 pl-4" :style="{ borderColor: doc.style.primaryColor }">
                    <div class="flex justify-between items-baseline">
                        <h3 class="font-semibold text-gray-900">{{ exp.position }}</h3>
                        <span class="text-xs text-gray-500">{{ exp.startDate }} - {{ exp.endDate || 'Présent' }}</span>
                    </div>
                    <p class="text-xs font-medium text-gray-700">{{ exp.company }}</p>
                    <p class="text-xs text-gray-600 mt-1 whitespace-pre-line">{{ exp.description }}</p>
                </div>
            </div>
        </section>
    </div>
</template>