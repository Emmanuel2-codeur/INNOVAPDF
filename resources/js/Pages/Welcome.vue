<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowUpRight, Sparkles, TrendingUp, Users, ShieldCheck,
    FileText, Mail, Receipt, ClipboardList, ScrollText, Award,
    Play, CheckCircle2,
} from 'lucide-vue-next';

const services = [
    { type: 'cv', template: 'monochrome', icon: FileText, title: 'Créer mon CV' },
    { type: 'cover_letter', template: 'rounded-green', icon: Mail, title: 'Lettre de motivation' },
    { type: 'invoice', template: 'purple', icon: Receipt, title: 'Créer une facture' },
    { type: 'quote', template: 'purple', icon: ClipboardList, title: 'Créer un devis' },
    { type: 'attestation', template: 'gold-black', icon: ScrollText, title: 'Attestation' },
    { type: 'certificate', template: 'gold-black', icon: Award, title: 'Certificat' },
];


</script>

<template>
    <Head title="INNOVAPDF — Créez vos documents professionnels" />

    <div class="min-h-screen bg-[#0a0a0a] text-white overflow-x-hidden relative">
        <!-- Motif de carrés en arrière-plan (comme la référence) -->
        <div class="pointer-events-none absolute inset-0 z-0 opacity-40"
             style="background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px); background-size: 32px 32px;">
        </div>
        <div class="pointer-events-none absolute top-0 left-0 right-0 h-[900px] z-0"
             style="background: radial-gradient(ellipse 60% 50% at 50% 20%, rgba(34,197,94,0.18), transparent 70%);">
        </div>

        <div class="relative z-10">
            <!-- Nav -->
            <header class="max-w-7xl mx-auto flex items-center justify-between px-6 py-6">
                <div class="flex items-center gap-2">
                    <span class="text-[#22c55e] text-2xl">⟿</span>
                    <span class="text-lg font-semibold tracking-tight">INNOVAPDF</span>
                </div>
                <nav class="hidden md:flex items-center gap-8 text-sm text-gray-300">
                    <a href="#services" class="hover:text-white transition">Services</a>
                    <a href="#features" class="hover:text-white transition">Fonctionnalités</a>
                    <Link href="/login" class="hover:text-white transition">Connexion</Link>
                </nav>
                <Link
                    href="/register"
                    class="flex items-center gap-1.5 bg-[#22c55e] text-black text-sm font-semibold px-4 py-2 rounded-full hover:brightness-110 transition"
                >
                    Créer un compte <ArrowUpRight class="w-4 h-4" />
                </Link>
            </header>

            <!-- Hero -->
            <section class="max-w-4xl mx-auto text-center px-6 pt-12 pb-16">
                <span class="inline-flex items-center gap-1.5 text-xs bg-white/5 border border-white/10 rounded-full px-4 py-1.5 mb-7 text-gray-300 backdrop-blur">
                    <Sparkles class="w-3.5 h-3.5 text-[#22c55e]" /> Assistant IA intégré à chaque document
                </span>
                <h1 class="text-4xl md:text-6xl font-bold leading-[1.1] mb-6 tracking-tight">
                    Les documents pro<br />que ta plateforme mérite
                </h1>
                <p class="text-gray-400 max-w-xl mx-auto mb-8 text-[15px]">
                    CV, lettres de motivation, factures, devis, attestations et certificats — création visuelle
                    instantanée, assistance IA, export PDF haute fidélité.
                </p>
                <div class="flex items-center justify-center gap-3">
                    <Link
                        href="/editor/new?type=cv&template=monochrome"
                        class="bg-[#22c55e] text-black font-semibold px-6 py-3 rounded-full text-sm hover:brightness-110 transition"
                    >
                        Créer mon document
                    </Link>
                    <a href="#services" class="border border-white/15 text-white px-6 py-3 rounded-full text-sm hover:bg-white/5 transition">
                        Voir les modèles
                    </a>
                </div>
            </section>

            <!-- Mockup dashboard (glow vert, cadre sombre) -->
            <section class="max-w-4xl mx-auto px-6 pb-16">
                <div class="relative rounded-2xl overflow-hidden border border-white/10 shadow-2xl"
                     style="box-shadow: 0 0 120px -20px rgba(34,197,94,0.35);">
                    <div class="bg-[#0f1210] p-5">
                        <div class="flex items-center justify-between mb-5">
                            <span class="flex items-center gap-1.5 text-[#22c55e] text-sm font-semibold">⟿ INNOVAPDF</span>
                            <div class="flex gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-white/10"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-white/10"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-white/10"></span>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div class="bg-white/5 rounded-lg p-4 h-24"><img :src="'@service1.jpg'" alt="" class="w-full h-full object-contain" /></div>
                            <div class="bg-white/5 rounded-lg p-4 h-24"><img :src="'@service2.jpg'" alt="" class="w-full h-full object-contain" /></div>
                            <div class="bg-white/5 rounded-lg p-4 h-24"><img :src="'@service3.jpg'" alt="" class="w-full h-full object-contain" /></div>
                        </div>
                    </div>
                </div>
            </section>

        

            <!-- Services -->
            <section id="services" class="max-w-6xl mx-auto px-6 pb-24">
                <h2 class="text-3xl font-bold text-center mb-3 tracking-tight">Que veux-tu créer ?</h2>
                <p class="text-center text-gray-400 mb-12 text-sm">Choisis un service, l'éditeur s'ouvre directement — sans compte requis.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <Link
                        v-for="service in services"
                        :key="service.type"
                        :href="`/editor/new?type=${service.type}&template=${service.template}`"
                        class="group bg-[#12151a] border border-white/10 rounded-2xl p-6 hover:border-[#22c55e]/50 transition relative overflow-hidden"
                    >
                        <div class="absolute -right-4 -top-4 w-16 h-16 bg-[#22c55e]/10 rounded-lg rotate-12 group-hover:bg-[#22c55e]/20 transition"></div>
                        <component :is="service.icon" class="w-7 h-7 text-[#22c55e] mb-4 relative" />
                        <h3 class="font-semibold relative group-hover:text-[#22c55e] transition">{{ service.title }}</h3>
                        <span class="text-xs text-gray-500 flex items-center gap-1 mt-2 relative">
                            Commencer <ArrowUpRight class="w-3 h-3" />
                        </span>
                    </Link>
                </div>
            </section>

            <!-- Features -->
            <section id="features" class="max-w-6xl mx-auto px-6 pb-24">
                <h2 class="text-2xl font-bold text-center mb-10">Accède au futur de la création documentaire</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-gradient-to-br from-[#16311f] to-[#0f1a13] border border-[#22c55e]/20 rounded-2xl p-8">
                        <span class="inline-block text-[10px] uppercase tracking-wider bg-white/10 rounded-full px-2.5 py-1 mb-4">Évolutivité</span>
                        <h3 class="text-xl font-semibold mb-2">Des modèles professionnels prêts à l'emploi</h3>
                        <p class="text-sm text-gray-300">CV, factures, devis, attestations : choisis un design éprouvé et personnalise-le en direct.</p>
                    </div>
                    <div class="bg-[#12151a] border border-white/10 rounded-2xl p-8 flex items-center justify-center">
                        <div class="bg-black/40 rounded-full p-4">
                            <Play class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-[#12151a] border border-white/10 rounded-2xl p-6">
                        <TrendingUp class="w-5 h-5 text-[#22c55e] mb-3" />
                        <p class="font-bold text-2xl mb-1">90%</p>
                        <p class="text-xs text-gray-400">de temps gagné sur la mise en page</p>
                    </div>
                    <div class="bg-[#12151a] border border-white/10 rounded-2xl p-6">
                        <Users class="w-5 h-5 text-[#22c55e] mb-3" />
                        <p class="font-bold text-2xl mb-1">Multi-documents</p>
                        <p class="text-xs text-gray-400">CV, lettres, factures, devis, attestations</p>
                    </div>
                    <div class="bg-[#22c55e] text-black rounded-2xl p-6">
                        <ShieldCheck class="w-5 h-5 mb-3" />
                        <p class="font-bold text-lg mb-1">Partage sécurisé</p>
                        <p class="text-xs">Lien privé + QR code, sans exposer ton compte.</p>
                    </div>
                </div>
            </section>

            <footer class="text-center text-xs text-gray-500 pb-10 flex items-center justify-center gap-1.5">
                <CheckCircle2 class="w-3.5 h-3.5" /> © {{ new Date().getFullYear() }} INNOVAPDF
            </footer>
        </div>
    </div>
</template>
