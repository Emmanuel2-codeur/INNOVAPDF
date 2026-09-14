<script setup lang="ts">
import type { DocumentSchema } from '@/types/document';
import { Phone, Mail, Globe, MapPin } from 'lucide-vue-next';

defineProps<{ doc: DocumentSchema }>();
</script>

<template>
    <div class="h-full w-full bg-white text-gray-800 flex" :style="{ fontFamily: doc.style.fontFamily }">
        <!-- Sidebar sombre -->
        <div class="w-[38%] bg-[#1c1c1c] text-white p-6 flex flex-col rounded-tr-[60px]">
            <img
                v-if="doc.profile?.photoUrl"
                :src="doc.profile.photoUrl"
                class="w-full aspect-square object-cover rounded-lg mb-6 grayscale"
                alt="Photo"
            />
            <div v-if="doc.education?.length" class="mb-6">
                <span class="inline-block text-black text-[10px] font-bold uppercase px-2.5 py-1 rounded mb-3" :style="{ backgroundColor: doc.style.primaryColor }">Education</span>
                <div v-for="edu in doc.education" :key="edu.id" class="mb-3">
                    <p class="text-xs font-semibold">{{ edu.degree }}</p>
                    <p class="text-[10px] text-gray-400">{{ edu.school }}</p>
                    <p class="text-[10px] text-gray-500">{{ edu.startDate }} - {{ edu.endDate }}</p>
                </div>
            </div>

            <div class="mt-auto space-y-2 pt-4 border-t border-white/10 text-[10px]">
                <p v-if="doc.profile?.phone" class="flex items-center gap-2 rounded px-2 py-1" :style="{ backgroundColor: doc.style.primaryColor + '30' }">
                    <Phone class="w-3 h-3" :style="{ color: doc.style.primaryColor }" />{{ doc.profile.phone }}
                </p>
                <p v-if="doc.profile?.email" class="flex items-center gap-2 rounded px-2 py-1" :style="{ backgroundColor: doc.style.primaryColor + '30' }">
                    <Mail class="w-3 h-3" :style="{ color: doc.style.primaryColor }" />{{ doc.profile.email }}
                </p>
                <p v-if="doc.profile?.location" class="flex items-center gap-2 rounded px-2 py-1" :style="{ backgroundColor: doc.style.primaryColor + '30' }">
                    <MapPin class="w-3 h-3" :style="{ color: doc.style.primaryColor }" />{{ doc.profile.location }}
                </p>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="flex-1 p-8">
            <span class="inline-block text-black text-[11px] font-bold uppercase px-3 py-1 rounded mb-2" :style="{ backgroundColor: doc.style.primaryColor }">
                {{ doc.profile?.title || 'Titre du poste' }}
            </span>
            <h1 class="text-3xl font-extrabold uppercase tracking-tight mb-6">{{ doc.profile?.fullName || 'Prénom Nom' }}</h1>

            <div v-if="doc.profile?.summary" class="mb-6">
                <span class="inline-block text-black text-[10px] font-bold uppercase px-2.5 py-1 rounded mb-2" :style="{ backgroundColor: doc.style.primaryColor }">About Me</span>
                <p class="text-xs text-gray-600 leading-relaxed">{{ doc.profile.summary }}</p>
            </div>

            <div v-if="doc.experiences?.length" class="mb-6">
                <span class="inline-block text-black text-[10px] font-bold uppercase px-2.5 py-1 rounded mb-3" :style="{ backgroundColor: doc.style.primaryColor }">Work Experience</span>
                <div v-for="exp in doc.experiences" :key="exp.id" class="flex gap-4 mb-3 text-xs">
                    <span class="text-gray-400 w-20 shrink-0">{{ exp.startDate }}-{{ exp.endDate || 'Présent' }}</span>
                    <div>
                        <p class="font-bold">{{ exp.position }}</p>
                        <p class="text-gray-500 text-[10px]">{{ exp.company }}</p>
                        <p class="text-gray-500 text-[10px] mt-0.5">{{ exp.description }}</p>
                    </div>
                </div>
            </div>

            <div v-if="doc.profile?.skills?.length">
                <span class="inline-block text-black text-[10px] font-bold uppercase px-2.5 py-1 rounded mb-2" :style="{ backgroundColor: doc.style.primaryColor }">Software Skill</span>
                <div class="grid grid-cols-2 gap-2 text-xs text-gray-600">
                    <p v-for="(skill, i) in doc.profile.skills" :key="i">{{ skill }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
