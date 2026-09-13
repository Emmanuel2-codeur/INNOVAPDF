<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Premium', href: '/premium' },
        ],
    },
});
</script>

<template>
    <Head title="Passer à Premium" />

    <div class="max-w-2xl mx-auto p-6 space-y-6">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-800">Passer à INNOVAPDF Premium</h1>
            <p class="text-gray-500 mt-2">Documents illimités, IA sans limite mensuelle, templates avancés.</p>
        </div>

        <div class="border rounded-xl p-6 bg-indigo-50 border-indigo-200">
            <ul class="space-y-2 text-sm text-gray-700">
                <li>✓ Documents illimités (plan gratuit : 3 max)</li>
                <li>✓ Appels IA illimités (plan gratuit : 20/mois)</li>
                <li>✓ Templates premium (bientôt)</li>
                <li>✓ Statistiques de profil public (bientôt)</li>
            </ul>
        </div>

        <div class="border rounded-xl p-6 text-center bg-amber-50 border-amber-200 text-amber-800 text-sm">
            Le paiement par Mobile Money n'est pas encore branché sur cette instance.
            Cette page prépare la structure (plans, quotas) ; l'intégration du
            paiement réel (CinetPay/FedaPay/Kkiapay) est prévue dans une prochaine étape.
        </div>

        <div class="text-center">
            <Link href="/dashboard" class="text-sm text-gray-500 hover:underline">← Retour au dashboard</Link>
        </div>
    </div>
</template>
