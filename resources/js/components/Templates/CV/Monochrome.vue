<script setup lang="ts">
import type { DocumentSchema } from '@/types/document';
import { Mail, Phone, Globe, MapPin } from 'lucide-vue-next';

defineProps<{ doc: DocumentSchema }>();
</script>

<template>
    <div class="h-full w-full bg-white text-gray-900 relative overflow-hidden" :style="{ fontFamily: doc.style.fontFamily }">
        <div class="absolute left-0 top-0 bottom-0 w-1.5" :style="{ backgroundColor: doc.style.primaryColor }"></div>

        <div class="p-10 pl-14">
            <!-- En-tête -->
            <div class="flex justify-between items-start mb-8">
                <div>
                    <p class="text-xs tracking-[0.2em] text-gray-400 uppercase mb-1">Position Title</p>
                    <p class="text-sm text-gray-500 mb-1">Hello I'm</p>
                    <h1 class="text-3xl font-bold tracking-tight">{{ doc.profile?.fullName || 'Prénom Nom' }}</h1>
                </div>
                <div class="text-right text-xs text-gray-500 space-y-1 mt-1">
                    <p class="flex items-center justify-end gap-1.5"><Phone class="w-3 h-3" />{{ doc.profile?.phone }}</p>
                    <p class="flex items-center justify-end gap-1.5"><Mail class="w-3 h-3" />{{ doc.profile?.email }}</p>
                    <p v-if="doc.profile?.location" class="flex items-center justify-end gap-1.5"><MapPin class="w-3 h-3" />{{ doc.profile.location }}</p>
                </div>
            </div>

            <p v-if="doc.profile?.summary" class="text-xs text-gray-500 leading-relaxed mb-8 max-w-md">{{ doc.profile.summary }}</p>

            <!-- Education (frise) -->
            <div v-if="doc.education?.length" class="mb-8">
                <h2 class="text-sm font-bold uppercase tracking-wide mb-4">Education</h2>
                <div class="flex gap-6">
                    <div v-for="edu in doc.education" :key="edu.id" class="flex-1 border-t-2 pt-2" :style="{ borderColor: doc.style.primaryColor }">
                        <p class="text-xs font-bold">{{ edu.degree }}</p>
                        <p class="text-[10px] text-gray-500">{{ edu.school }}</p>
                        <p class="text-[10px] text-gray-400">{{ edu.startDate }} - {{ edu.endDate }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-8">
                <!-- Colonne gauche : contact + expertise -->
                <div class="col-span-1 space-y-6">
                    <div v-if="doc.profile?.skills?.length">
                        <h2 class="text-sm font-bold uppercase tracking-wide mb-3">Expertise</h2>
                        <ul class="space-y-1.5 text-xs text-gray-600">
                            <li v-for="(skill, i) in doc.profile.skills" :key="i">• {{ skill }}</li>
                        </ul>
                    </div>
                </div>

                <!-- Colonne droite : expérience -->
                <div class="col-span-2">
                    <h2 v-if="doc.experiences?.length" class="text-sm font-bold uppercase tracking-wide mb-3">Work Experience</h2>
                    <div v-for="exp in doc.experiences" :key="exp.id" class="mb-4 pl-4 border-l" style="border-color:#e5e7eb;">
                        <span class="w-1.5 h-1.5 rounded-full inline-block -ml-[19px] mr-2 relative top-[-1px]" :style="{ backgroundColor: doc.style.primaryColor }"></span>
                        <p class="text-xs font-bold inline">{{ exp.position }}</p>
                        <p class="text-[10px] text-gray-500">{{ exp.company }} | {{ exp.startDate }} - {{ exp.endDate || 'Présent' }}</p>
                        <p class="text-[10px] text-gray-500 mt-1 whitespace-pre-line">{{ exp.description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
