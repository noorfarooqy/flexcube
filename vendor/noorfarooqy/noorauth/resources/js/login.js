import './bootstrap';
import {createApp} from 'vue/dist/vue.esm-bundler.js';
import Login from './Components/Login.vue';
var app = createApp({});

app.component('login-form', Login);

app.mount('#Login');