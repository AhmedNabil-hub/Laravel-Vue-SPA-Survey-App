<template>
	<div>
		<div>
			<svg
				xmlns="http://www.w3.org/2000/svg"
				fill="none"
				viewBox="0 0 24 24"
				stroke-width="1.5"
				stroke="currentColor"
				class="mx-auto h-12 w-auto"
			>
				<path
					stroke-linecap="round"
					stroke-linejoin="round"
					d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"
				/>
			</svg>

			<h2
				class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900"
			>
				Sign in to your account
			</h2>
			<p class="mt-2 text-center text-sm text-gray-600">
				Or {{ " " }}
				<router-link
					:to="{ name: 'Register' }"
					class="font-medium text-indigo-600 hover:text-indigo-500"
				>
					Register
				</router-link>
			</p>
		</div>
		<form class="mt-8 space-y-6" @submit.prevent="login">
			<Alert v-if="errorMsg">
				{{ errorMsg }}
				<span
					@click="errorMsg = ''"
					class="w-8 h-8 flex items-center justify-center rounded-full transition-colors cursor-pointer hover:bg-[rgba(0,0,0,0.2)]"
				>
					<svg
						xmlns="http://www.w3.org/2000/svg"
						fill="none"
						viewBox="0 0 24 24"
						stroke-width="1.5"
						stroke="currentColor"
						class="w-6 h-6"
					>
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							d="M6 18L18 6M6 6l12 12"
						/>
					</svg>
				</span>
			</Alert>
			<div class="-space-y-px rounded-md shadow-sm">
				<div>
					<label for="email" class="sr-only">Email address</label>
					<input
						id="email"
						name="email"
						type="email"
						autocomplete="email"
						required=""
						class="relative block w-full appearance-none rounded-none rounded-t-md border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:z-10 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
						placeholder="Email address"
						v-model="user.email"
					/>
				</div>
				<div>
					<label for="password" class="sr-only">Password</label>
					<input
						id="password"
						name="password"
						type="password"
						autocomplete="current-password"
						required=""
						class="relative block w-full appearance-none rounded-none rounded-b-md border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:z-10 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
						placeholder="Password"
						v-model="user.password"
					/>
				</div>
			</div>
			<div class="flex items-center justify-between">
				<div class="flex items-center">
					<input
						id="remember"
						name="remember"
						type="checkbox"
						class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
						v-model="remember"
					/>
					<label for="remember" class="ml-2 block text-sm text-gray-900"
						>Remember me</label
					>
				</div>
			</div>
			<div>
				<button
					:disabled="loading"
					:class="{
						'cursor-not-allowed': loading,
						'hover:bg-indigo-500': loading,
					}"
					type="submit"
					class="group relative flex w-full justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
				>
					<span class="absolute inset-y-0 left-0 flex items-center pl-3">
						<LockClosedIcon
							class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400"
							aria-hidden="true"
						/>
					</span>
					<svg
						v-if="loading"
						class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
						xmlns="http://www.w3.org/2000/svg"
						fill="none"
						viewBox="0 0 24 24"
					>
						<circle
							class="opacity-25"
							cx="12"
							cy="12"
							r="10"
							stroke="currentColor"
							stroke-width="4"
						></circle>
						<path
							class="opacity-75"
							fill="currentColor"
							d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
						></path>
					</svg>
					Sign in
				</button>
			</div>
		</form>
	</div>
</template>

<script setup>
import { LockClosedIcon } from "@heroicons/vue/20/solid";
import { ref } from "vue";
import { useRouter } from "vue-router";
import store from "../store";
import Alert from "../components/Alert.vue";

const router = useRouter();
const user = {
	email: "",
	pasword: "",
	remember: false,
};

const loading = ref(false);

let errorMsg = ref("");

function login() {
	loading.value = true;

	store
		.dispatch("login", user)
		.then(() => {
			loading.value = false;
			router.push({
				name: "Dashboard",
			});
		})
		.catch((error) => {
			loading.value = false;
			errorMsg.value = error.response.data.error;
		});
}
</script>

<style scoped></style>
