<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    project: Object
});

const form = useForm({
    name: '',
    type: 'INTERIOR',
    length: '',
    breadth: '',
    height: ''
});

const submit = () => {
    form.post(route('supervisor.zones.store', props.project.id), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 pb-24">
        <!-- Header - Screen A Wireframe -->
        <div class="bg-white border-b p-4 shadow-sm sticky top-0 z-10">
            <Link :href="route('supervisor.projects.show', project.id)" class="text-blue-600 text-sm font-medium block mb-1">← Back</Link>
            <h1 class="text-xl font-bold text-gray-900">CREATE ZONE</h1>
        </div>

        <div class="max-w-lg mx-auto px-4 py-4">
            <!-- Form Layout - Screen A Wireframe -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 space-y-4">
                <!-- 1. Name Input - Screen A Wireframe -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">1. Name</label>
                    <input 
                        v-model="form.name" 
                        type="text" 
                        placeholder="e.g., Master Bedroom"
                        class="w-full h-12 px-4 border border-gray-300 rounded-lg text-base focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                <!-- 2. Type Toggle - Screen A Wireframe -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">2. Type</label>
                    <div class="flex gap-3">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" v-model="form.type" value="INTERIOR" class="sr-only peer">
                            <div class="h-12 flex items-center justify-center border-2 border-gray-200 rounded-lg peer-checked:border-blue-600 peer-checked:bg-blue-50 text-gray-600 peer-checked:text-blue-600 font-medium transition">
                                ● Interior
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" v-model="form.type" value="EXTERIOR" class="sr-only peer">
                            <div class="h-12 flex items-center justify-center border-2 border-gray-200 rounded-lg peer-checked:border-blue-600 peer-checked:bg-blue-50 text-gray-600 peer-checked:text-blue-600 font-medium transition">
                                ○ Exterior
                            </div>
                        </label>
                    </div>
                </div>

                <!-- 3. Default Dimensions - Screen A Wireframe -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">3. Default Dimensions (Optional)</label>
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <input 
                                v-model="form.length" 
                                type="number" 
                                step="0.01" 
                                placeholder="L"
                                class="w-full h-12 px-3 border border-gray-300 rounded-lg text-center text-base focus:ring-blue-500 focus:border-blue-500"
                            >
                        </div>
                        <div class="flex-1">
                            <input 
                                v-model="form.breadth" 
                                type="number" 
                                step="0.01" 
                                placeholder="B"
                                class="w-full h-12 px-3 border border-gray-300 rounded-lg text-center text-base focus:ring-blue-500 focus:border-blue-500"
                            >
                        </div>
                        <div class="flex-1">
                            <input 
                                v-model="form.height" 
                                type="number" 
                                step="0.01" 
                                placeholder="H"
                                class="w-full h-12 px-3 border border-gray-300 rounded-lg text-center text-base focus:ring-blue-500 focus:border-blue-500"
                            >
                        </div>
                    </div>
                </div>

                <!-- Create Zone Button - Screen A Wireframe -->
                <button 
                    @click="submit" 
                    :disabled="form.processing || !form.name"
                    class="w-full h-12 bg-blue-600 text-white rounded-lg font-bold shadow hover:bg-blue-700 disabled:opacity-50 transition text-base"
                >
                    CREATE ZONE
                </button>
            </div>
        </div>
    </div>
</template>
