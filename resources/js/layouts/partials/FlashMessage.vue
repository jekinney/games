<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { CheckCircle, X } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const page = usePage();
const showMessage = ref(true);

const successMessage = computed(() => {
    const flash = page.props.flash as any;
    return flash?.success || null;
});

const closeMessage = () => {
    showMessage.value = false;
};
</script>

<template>
    <div
        v-if="successMessage && showMessage"
        class="fixed top-4 right-4 z-50 flex items-center rounded-lg border border-green-500/30 bg-green-500/20 px-4 py-3 text-green-300 shadow-lg backdrop-blur-sm"
    >
        <CheckCircle class="mr-3 h-5 w-5" />
        <span>{{ successMessage }}</span>
        <button
            @click="closeMessage"
            class="ml-4 rounded-full p-1 hover:bg-green-500/30"
        >
            <X class="h-4 w-4" />
        </button>
    </div>
</template>
