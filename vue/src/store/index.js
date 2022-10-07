import { createStore } from "vuex";
import axiosClient from "../axios";

const tmpSurveys = [
	{
		id: 100,
		title: "The Codeholic youtube channel content",
		slug: "thecodeholic-youtube-channel-content",
		status: "draft",
		image: "https://picsum.photos/200",
		description: "My name is Ahmed.<br>I am a Web Developer",
		created_at: "2022-10-06 18:00:00",
		updated_at: "2022-10-06 18:00:00",
		expire_date: "2022-10-08 18:00:00",
		questions: [
			{
				id: 1,
				type: "select",
				question: "From which country are you ?",
				description: null,
				data: {
					options: [
						{
							uuid: "f8af96f2-1d80-4632-9e9e-b560670e52ea",
							text: "Egypt",
						},
					],
				},
			},
		],
	},
];

const store = createStore({
	state: {
		user: {
			data: {},
			token: sessionStorage.getItem("TOKEN"),
		},
		surveys: [...tmpSurveys],
		questionTypes: ["text", "select", "radio", "checkbox", "textarea"],
	},
	getters: {},
	actions: {
		register({ commit }, user) {
			// return fetch(`http://localhost:8000/api/register`, {
			// 	headers: {
			// 		"Content-Type": "application/json",
			// 		Accept: "application/json",
			// 	},
			// 	method: "POST",
			// 	body: JSON.stringify(user),
			// })
			// 	.then((res) => res.json())
			// 	.then((res) => {
			// 		commit("setUser", res);
			// 		return res;
			// 	});
			return axiosClient.post("/register", user).then(({ data }) => {
				commit("setUser", data);
				return data;
			});
		},
		login({ commit }, user) {
			return axiosClient.post("/login", user).then(({ data }) => {
				commit("setUser", data);
				return data;
			});
		},
		logout({ commit }) {
			return axiosClient.post("/logout").then((response) => {
				commit("logout");
				return response;
			});
		},
	},
	mutations: {
		logout: (state) => {
			sessionStorage.clear();
			state.user.data = {};
			state.user.token = null;
		},
		setUser: (state, userData) => {
			state.user.token = userData.token;
			state.user.data = userData.user;
			sessionStorage.setItem("TOKEN", userData.token);
		},
	},
	modules: {},
});

export default store;
