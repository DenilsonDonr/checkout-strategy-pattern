<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div class="w-full max-w-lg bg-white rounded-2xl shadow-sm border border-gray-200 p-8">

            <h1 class="text-2xl font-semibold text-gray-900 mb-1">Checkout</h1>
            <p class="text-sm text-gray-500 mb-8">Complete your order details below.</p>

            <!-- Payment error -->
            <div v-if="form.errors.payment || cardError" class="mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                {{ form.errors.payment || cardError }}
            </div>

            <form @submit.prevent="submit" class="space-y-5">

                <!-- Customer info -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full name</label>
                    <input
                        v-model="form.customer_name"
                        type="text"
                        placeholder="John Doe"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent"
                        :class="{ 'border-red-400': form.errors.customer_name }"
                    />
                    <p v-if="form.errors.customer_name" class="mt-1 text-xs text-red-500">{{ form.errors.customer_name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input
                        v-model="form.customer_email"
                        type="email"
                        placeholder="john@example.com"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent"
                        :class="{ 'border-red-400': form.errors.customer_email }"
                    />
                    <p v-if="form.errors.customer_email" class="mt-1 text-xs text-red-500">{{ form.errors.customer_email }}</p>
                </div>

                <!-- Amount + Currency -->
                <div class="flex gap-3">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                        <input
                            v-model="form.total_price"
                            type="number"
                            step="0.01"
                            min="0.01"
                            placeholder="0.00"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent"
                            :class="{ 'border-red-400': form.errors.total_price }"
                        />
                        <p v-if="form.errors.total_price" class="mt-1 text-xs text-red-500">{{ form.errors.total_price }}</p>
                    </div>
                    <div class="w-28">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                        <select
                            v-model="form.currency"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
                        >
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="PEN">PEN – Soles</option>
                        </select>
                    </div>
                </div>

                <!-- Payment method -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment method</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            v-for="method in paymentMethods"
                            :key="method.value"
                            type="button"
                            @click="form.payment_method = method.value"
                            class="flex flex-col items-center justify-center rounded-lg border px-3 py-3 text-xs font-medium transition-all"
                            :class="form.payment_method === method.value
                                ? 'border-gray-900 bg-gray-900 text-white'
                                : 'border-gray-200 text-gray-600 hover:border-gray-400'"
                        >
                            <span class="text-lg mb-1">{{ methodIcon(method.value) }}</span>
                            {{ method.label }}
                        </button>
                    </div>
                    <p v-if="form.errors.payment_method" class="mt-1 text-xs text-red-500">{{ form.errors.payment_method }}</p>
                </div>

                <!-- Stripe Card Element -->
                <div v-if="form.payment_method === 'stripe'" class="space-y-3 rounded-lg bg-gray-50 border border-gray-200 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Card details</p>
                    <div
                        ref="cardElementRef"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm"
                    ></div>
                    <p class="text-xs text-gray-400">
                        Test: <code class="bg-gray-200 px-1 rounded">4242 4242 4242 4242</code> — cualquier fecha futura y CVC.
                        Decline: <code class="bg-gray-200 px-1 rounded">4000 0000 0000 0002</code>
                    </p>
                </div>

                <!-- PayPal -->
                <div v-if="form.payment_method === 'paypal'" class="space-y-3 rounded-lg bg-gray-50 border border-gray-200 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">PayPal account</p>
                    <input
                        v-model="form.payload.paypal_email"
                        type="email"
                        placeholder="your-paypal@example.com"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
                    />
                </div>

                <!-- Transfer -->
                <div v-if="form.payment_method === 'transfer'" class="rounded-lg bg-blue-50 border border-blue-200 p-4 text-sm text-blue-700">
                    <p class="font-medium mb-1">Bank transfer instructions</p>
                    <p class="text-xs">A reference number will be generated after you place the order. Transfer the amount to account <strong>****4242</strong> using that reference.</p>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes <span class="text-gray-400">(optional)</span></label>
                    <textarea
                        v-model="form.notes"
                        rows="2"
                        placeholder="Any special instructions..."
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 resize-none"
                    />
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    :disabled="form.processing || isSubmitting"
                    class="w-full rounded-lg bg-gray-900 text-white py-3 text-sm font-medium hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <span v-if="form.processing || isSubmitting">Processing...</span>
                    <span v-else>Pay {{ formattedTotal }}</span>
                </button>

            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { loadStripe } from '@stripe/stripe-js'
import { computed, nextTick, onMounted, ref, watch } from 'vue'

const props = defineProps({
    paymentMethods: Array,
    stripeKey:      String,
})

const form = useForm({
    customer_name:  '',
    customer_email: '',
    total_price:    '',
    currency:       'USD',
    payment_method: 'stripe',
    notes:          '',
    payload:        {
        payment_method_id: '',
        paypal_email:      '',
    },
})

// Stripe
const cardElementRef = ref(null)
const cardError      = ref(null)
const isSubmitting   = ref(false)
let stripe      = null
let cardElement = null

onMounted(async () => {
    if (props.stripeKey) {
        stripe = await loadStripe(props.stripeKey)
        await nextTick()
        mountCardElement()
    }
})

// Re-mount when switching back to stripe tab
watch(() => form.payment_method, async (method) => {
    if (method === 'stripe') {
        await nextTick()
        mountCardElement()
    }
})

function mountCardElement() {
    if (!stripe || !cardElementRef.value) return
    if (cardElement) cardElement.unmount()

    const elements = stripe.elements()
    cardElement = elements.create('card', {
        style: {
            base: {
                fontSize:    '14px',
                color:       '#111827',
                '::placeholder': { color: '#9ca3af' },
            },
        },
    })
    cardElement.mount(cardElementRef.value)
    cardElement.on('change', (e) => {
        cardError.value = e.error ? e.error.message : null
    })
}

const formattedTotal = computed(() => {
    const amount = parseFloat(form.total_price)
    if (!amount) return ''
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: form.currency }).format(amount)
})

const methodIcon = (value) => ({ stripe: '💳', paypal: '🅿️', transfer: '🏦' }[value] ?? '💰')

async function submit() {
    cardError.value = null

    // Stripe: tokenize card on frontend before submitting
    if (form.payment_method === 'stripe') {
        if (!stripe || !cardElement) return

        isSubmitting.value = true
        const { paymentMethod, error } = await stripe.createPaymentMethod({
            type: 'card',
            card: cardElement,
        })
        isSubmitting.value = false

        if (error) {
            cardError.value = error.message
            return
        }

        form.payload.payment_method_id = paymentMethod.id
    }

    form.post('/checkout')
}
</script>
