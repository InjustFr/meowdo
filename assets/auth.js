import { createApp } from 'vue';
import './styles/fonts.css';
import './styles/tokens.css';
import './styles/base.css';
import ForgotPasswordPage from './vue/pages/auth/ForgotPasswordPage.vue';
import LoginPage from './vue/pages/auth/LoginPage.vue';
import SetPasswordPage from './vue/pages/auth/SetPasswordPage.vue';
import { i18n } from './vue/i18n/index.js';

const PAGES = { LoginPage, ForgotPasswordPage, SetPasswordPage };

const root = document.getElementById('auth');
createApp(PAGES[root.dataset.component], JSON.parse(root.dataset.props)).use(i18n).mount(root);
