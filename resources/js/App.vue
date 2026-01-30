<template>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-6xl px-6 py-10">
            <header class="mb-10 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.2em] text-slate-400">Notification Platform</p>
                    <h1 class="text-3xl font-semibold text-white">Управление событиями и уведомлениями</h1>
                </div>
                <div class="flex items-center gap-2 text-sm text-slate-300">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full" :class="statusIndicator"></span>
                    <span>{{ apiStatus }}</span>
                </div>
            </header>

            <main class="grid gap-6 lg:grid-cols-[1fr_1.1fr]">
                <section class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 shadow-xl shadow-slate-950/40">
                    <h2 class="text-lg font-semibold text-white">Создать событие</h2>
                    <p class="mt-1 text-sm text-slate-400">
                        Заполните данные события. Webhook URL попадёт в payload и переключит канал на webhook.
                    </p>

                    <form class="mt-6 space-y-4" @submit.prevent="submitEvent">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="text-xs uppercase tracking-wide text-slate-400">Type</label>
                                <input
                                    v-model.trim="form.type"
                                    class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-indigo-500 focus:outline-none"
                                    placeholder="user.created"
                                    required
                                />
                            </div>
                            <div>
                                <label class="text-xs uppercase tracking-wide text-slate-400">Source</label>
                                <input
                                    v-model.trim="form.source"
                                    class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-indigo-500 focus:outline-none"
                                    placeholder="crm"
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="text-xs uppercase tracking-wide text-slate-400">Occurred at</label>
                                <input
                                    v-model="form.occurred_at"
                                    type="datetime-local"
                                    class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
                                />
                            </div>
                            <div>
                                <label class="text-xs uppercase tracking-wide text-slate-400">Webhook URL</label>
                                <input
                                    v-model.trim="form.webhook_url"
                                    type="url"
                                    class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-indigo-500 focus:outline-none"
                                    placeholder="https://example.com/webhook"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="text-xs uppercase tracking-wide text-slate-400">Payload (JSON)</label>
                            <textarea
                                v-model="form.payload"
                                rows="6"
                                class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-indigo-500 focus:outline-none"
                                placeholder='{"order_id": 42, "total": 1200}'
                            ></textarea>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400 disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="submitting"
                            >
                                <span>{{ submitting ? 'Отправка...' : 'Отправить событие' }}</span>
                            </button>
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-lg border border-slate-700 px-4 py-2 text-sm text-slate-300 hover:border-slate-500"
                                @click="resetForm"
                            >
                                Очистить форму
                            </button>
                        </div>

                        <p v-if="formError" class="rounded-lg border border-rose-500/40 bg-rose-500/10 px-3 py-2 text-sm text-rose-200">
                            {{ formError }}
                        </p>
                        <p v-if="formSuccess" class="rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-3 py-2 text-sm text-emerald-200">
                            {{ formSuccess }}
                        </p>
                    </form>
                </section>

                <section class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 shadow-xl shadow-slate-950/40">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-white">Уведомления</h2>
                            <p class="mt-1 text-sm text-slate-400">Последние 20 уведомлений с событием.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                class="inline-flex items-center gap-2 rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-300 hover:border-slate-500"
                                @click="fetchNotifications"
                                :disabled="loadingNotifications"
                            >
                                {{ loadingNotifications ? 'Обновление...' : 'Обновить' }}
                            </button>
                        </div>
                    </div>

                    <div v-if="loadingNotifications" class="mt-6 text-sm text-slate-400">Загрузка...</div>
                    <div v-else-if="notifications.length === 0" class="mt-6 text-sm text-slate-400">
                        Пока нет уведомлений.
                    </div>

                    <div v-else class="mt-6 space-y-4">
                        <article
                            v-for="notification in notifications"
                            :key="notification.id"
                            class="rounded-xl border border-slate-800 bg-slate-950/40 p-4"
                        >
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-white">
                                        #{{ notification.id }} • {{ notification.channel }} → {{ notification.recipient }}
                                    </p>
                                    <p class="text-xs text-slate-400">
                                        Event: {{ notification.event?.type }} • {{ formatDate(notification.created_at) }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase" :class="statusClass(notification.status)">
                                        {{ notification.status }}
                                    </span>
                                    <button
                                        v-if="notification.status === 'failed'"
                                        class="rounded-lg border border-rose-500/60 px-3 py-1 text-xs text-rose-200 hover:border-rose-400"
                                        @click="retryNotification(notification)"
                                    >
                                        Retry
                                    </button>
                                </div>
                            </div>

                            <div class="mt-3 grid gap-3 text-xs text-slate-400 md:grid-cols-2">
                                <div>
                                    <p><span class="text-slate-500">Attempts:</span> {{ notification.attempts }}</p>
                                    <p v-if="notification.sent_at"><span class="text-slate-500">Sent:</span> {{ formatDate(notification.sent_at) }}</p>
                                </div>
                                <div>
                                    <p v-if="notification.last_error" class="text-rose-200">
                                        <span class="text-rose-400">Error:</span> {{ notification.last_error }}
                                    </p>
                                    <p v-else class="text-emerald-200">
                                        <span class="text-emerald-300">Last error:</span> none
                                    </p>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';

const form = ref({
    type: '',
    source: '',
    occurred_at: '',
    webhook_url: '',
    payload: '{\n  "message": "Hello"\n}',
});

const submitting = ref(false);
const formError = ref('');
const formSuccess = ref('');

const notifications = ref([]);
const loadingNotifications = ref(false);
const apiStatus = ref('Проверяем соединение...');
const apiReachable = ref(false);

const statusIndicator = computed(() => {
    if (apiStatus.value === 'Соединение установлено') {
        return 'bg-emerald-400';
    }
    if (apiStatus.value === 'Нет соединения') {
        return 'bg-rose-400';
    }
    return 'bg-amber-400';
});

const resetForm = () => {
    form.value = {
        type: '',
        source: '',
        occurred_at: '',
        webhook_url: '',
        payload: '{\n  "message": "Hello"\n}',
    };
    formError.value = '';
    formSuccess.value = '';
};

const statusClass = (status) => {
    switch (status) {
        case 'sent':
            return 'bg-emerald-500/20 text-emerald-200';
        case 'failed':
            return 'bg-rose-500/20 text-rose-200';
        case 'pending':
            return 'bg-amber-500/20 text-amber-200';
        default:
            return 'bg-slate-700 text-slate-200';
    }
};

const formatDate = (value) => {
    if (!value) return '-';
    return new Date(value).toLocaleString();
};

const buildPayload = () => {
    let parsedPayload = {};

    if (form.value.payload?.trim()) {
        parsedPayload = JSON.parse(form.value.payload);
    }

    if (form.value.webhook_url) {
        parsedPayload.webhook_url = form.value.webhook_url;
    }

    return parsedPayload;
};

const submitEvent = async () => {
    formError.value = '';
    formSuccess.value = '';

    let payloadData;
    try {
        payloadData = buildPayload();
    } catch (error) {
        formError.value = 'Payload должен быть валидным JSON.';
        return;
    }

    submitting.value = true;

    try {
        const response = await window.axios.post('/api/events', {
            type: form.value.type,
            source: form.value.source || null,
            occurred_at: form.value.occurred_at || null,
            payload: payloadData,
        });

        formSuccess.value = `Событие создано (#${response.data.id}).`;
        await fetchNotifications();
    } catch (error) {
        const message =
            error.response?.data?.message ||
            error.response?.data?.errors?.payload?.[0] ||
            'Не удалось создать событие.';
        formError.value = message;
    } finally {
        submitting.value = false;
    }
};

const fetchNotifications = async () => {
    loadingNotifications.value = true;
    try {
        const response = await window.axios.get('/api/notifications');
        notifications.value = response.data?.data ?? [];
        apiReachable.value = true;
        apiStatus.value = 'Соединение установлено';
    } catch (error) {
        apiReachable.value = false;
        apiStatus.value = 'Нет соединения';
    } finally {
        loadingNotifications.value = false;
    }
};

const retryNotification = async (notification) => {
    try {
        await window.axios.post(`/api/notifications/${notification.id}/retry`);
        await fetchNotifications();
    } catch (error) {
        formError.value = error.response?.data?.message || 'Не удалось отправить retry.';
    }
};

onMounted(async () => {
    await fetchNotifications();
});
</script>
