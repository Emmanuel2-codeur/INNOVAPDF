<script setup lang="ts">
import type { DocumentSchema } from '@/types/document';
import { MapPin, Phone, Mail, Globe } from 'lucide-vue-next';

defineProps<{ doc: DocumentSchema }>();
</script>

<template>
    <div class="h-full w-full bg-white text-gray-800 p-10" :style="{ fontFamily: doc.style.fontFamily }">
        <!-- En-tête -->
        <div class="flex items-center gap-6 mb-8">
            <img
                v-if="doc.profile?.photoUrl"
                :src="doc.profile.photoUrl"
                class="w-24 h-24 rounded-full object-cover grayscale border-4 border-gray-100"
                alt="Photo"
            />
            <div>
                <p class="text-xs text-gray-400 mb-1">Position Title</p>
                <p class="text-sm text-gray-500">Hello I'm</p>
                <h1 class="text-2xl font-bold">{{ doc.profile?.fullName || 'Prénom Nom' }}</h1>
            </div>
        </div>

        <p v-if="doc.profile?.summary" class="text-xs text-gray-500 leading-relaxed mb-8">{{ doc.profile.summary }}</p>

        <!-- Frise Éducation -->
        <div v-if="doc.education?.length" class="mb-8">
            <h2 class="text-sm font-bold uppercase tracking-wide mb-4">Education</h2>
            <div class="flex items-start">
                <template v-for="(edu, i) in doc.education" :key="edu.id">
                    <div class="flex-1 relative pt-4 text-xs">
                        <span class="absolute top-0 left-0 w-2.5 h-2.5 rounded-full bg-white border-2" :style="{ borderColor: doc.style.primaryColor }"></span>
                        <div class="absolute top-1 h-px bg-gray-200" :style="{ left: '10px', right: i === (doc.education?.length ?? 1) - 1 ? '50%' : '-50%' }"></div>
                        <p class="font-bold">{{ edu.degree }}</p>
                        <p class="text-gray-500 text-[10px]">{{ edu.school }}</p>
                        <p class="text-gray-400 text-[10px]">{{ edu.startDate }} - {{ edu.endDate }}</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- Deux colonnes -->
        <div class="grid grid-cols-2 gap-10">
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wide mb-3">Contact</h2>
                <ul class="space-y-2 text-xs text-gray-600 mb-6">
                    <li v-if="doc.profile?.location" class="flex items-center gap-2"><MapPin class="w-3.5 h-3.5" :style="{ color: doc.style.primaryColor }" />{{ doc.profile.location }}</li>
                    <li v-if="doc.profile?.phone" class="flex items-center gap-2"><Phone class="w-3.5 h-3.5" :style="{ color: doc.style.primaryColor }" />{{ doc.profile.phone }}</li>
                    <li v-if="doc.profile?.email" class="flex items-center gap-2"><Mail class="w-3.5 h-3.5" :style="{ color: doc.style.primaryColor }" />{{ doc.profile.email }}</li>
                </ul>

                <div v-if="doc.profile?.skills?.length">
                    <h2 class="text-sm font-bold uppercase tracking-wide mb-3">Expertise</h2>
                    <ul class="space-y-1 text-xs text-gray-600">
                        <li v-for="(skill, i) in doc.profile.skills" :key="i">• {{ skill }}</li>
                    </ul>
                </div>
            </div>

            <div>
                <h2 v-if="doc.experiences?.length" class="text-sm font-bold uppercase tracking-wide mb-3">Work Experience</h2>
                <div v-for="exp in doc.experiences" :key="exp.id" class="mb-4 relative pl-4">
                    <span class="absolute left-0 top-1 w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: doc.style.primaryColor }"></span>
                    <p class="text-xs font-bold">{{ exp.position }}</p>
                    <p class="text-[10px] text-gray-500">{{ exp.company }} | {{ exp.startDate }} - {{ exp.endDate || 'Présent' }}</p>
                    <p class="text-[10px] text-gray-500 mt-0.5 whitespace-pre-line">{{ exp.description }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
