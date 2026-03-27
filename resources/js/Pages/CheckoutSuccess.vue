<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div class="w-full max-w-lg bg-white rounded-2xl shadow-sm border border-gray-200 p-8 text-center">

            <!-- Icon -->
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full" :class="iconBg">
                <span class="text-2xl">{{ statusIcon }}</span>
            </div>

            <h1 class="text-2xl font-semibold text-gray-900 mb-1">{{ title }}</h1>
            <p class="text-sm text-gray-500 mb-8">{{ subtitle }}</p>

            <!-- Order summary -->
            <div class="rounded-xl bg-gray-50 border border-gray-200 p-5 text-left space-y-3 mb-6">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Order summary</p>

                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Customer</span>
                    <span class="font-medium text-gray-900">{{ order.customer_name }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Email</span>
                    <span class="font-medium text-gray-900">{{ order.customer_email }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Amount</span>
                    <span class="font-medium text-gray-900">{{ formattedAmount }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Gateway</span>
                    <span class="font-medium text-gray-900 capitalize">{{ payment?.gateway ?? '—' }}</span>
                </div>
                <div class="flex justify-between text-sm" v-if="payment?.gateway_reference">
                    <span class="text-gray-500">Reference</span>
                    <span class="font-mono text-xs text-gray-700">{{ payment.gateway_reference }}</span>
                </div>

                <!-- Transfer instructions -->
                <div v-if="payment?.status === 'pending'" class="rounded-lg bg-blue-50 border border-blue-200 p-3 mt-2">
                    <p class="text-xs text-blue-700 font-medium mb-1">Pending bank confirmation</p>
                    <p class="text-xs text-blue-600">
                        Transfer <strong>{{ formattedAmount }}</strong> to account <strong>****4242</strong>
                        using reference <strong>{{ payment.gateway_reference }}</strong>.
                    </p>
                </div>
            </div>

            <a
                href="/checkout"
                class="inline-block rounded-lg bg-gray-900 text-white px-6 py-2.5 text-sm font-medium hover:bg-gray-700 transition-colors"
            >
                New order
            </a>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    order:   Object,
    payment: Object,
})

const isPending = computed(() => props.payment?.status === 'pending')

const title    = computed(() => isPending.value ? 'Order received' : 'Payment confirmed')
const subtitle = computed(() => isPending.value
    ? 'Your order is pending bank transfer confirmation.'
    : 'Your payment was processed successfully.')

const iconBg     = computed(() => isPending.value ? 'bg-yellow-100' : 'bg-green-100')
const statusIcon = computed(() => isPending.value ? '⏳' : '✅')

const formattedAmount = computed(() =>
    new Intl.NumberFormat('en-US', {
        style:    'currency',
        currency: props.order?.currency ?? 'USD',
    }).format(props.order?.total_price ?? 0)
)
</script>
