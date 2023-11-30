<template>
    <div class="">
        <div class="mb-3">
            <label for="email" class="form-label">Email </label>
            <input type="text" class="form-control" id="email" name="email-username"
                placeholder="Enter your email or username" autofocus v-model="form_data.email" />
        </div>
        <div class="mb-3 form-password-toggle">
            <div class="d-flex justify-content-between">
                <label class="form-label" for="password">Password</label>
                <a href="auth-forgot-password-cover.html">
                    <small>Forgot Password?</small>
                </a>
            </div>
            <div class="input-group input-group-merge">
                <input type="password" id="password" class="form-control" name="password" placeholder="************"
                    aria-describedby="password" v-model="form_data.password" />
                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
            </div>
        </div>
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember-me" />
                <label class="form-check-label" for="remember-me"> Remember Me </label>
            </div>
        </div>
        <button class="btn btn-primary d-grid w-100" @click.prevent="Login()">Log in</button>
    </div>
</template>

<script>
import Server from '../server.js';
export default {
    data() {
        return {
            name: 'login',
            server: new Server(),
            loading_sms: false,
            form_data: {
                email: null,
                password: null,
            }
        };
    },
    mounted() {

    },

    methods: {

        Login() {
            this.server.setRequest(this.form_data);
            this.server.PostRequest('/api/v1/na/login', this.LoginSuccess, this.ErrorMessage);
        },

        LoginSuccess(data) {
            this.SuccessMessage('Login successful');
            window.location.reload();
        },
        StopLoaders() {
            this.loading_sms = false;
        },

        SuccessMessage(message) {

            Swal.fire({
                title: 'Success!',
                text: message ?? 'Process completed',
                icon: 'success',
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            });
            this.StopLoaders();
        },
        ErrorMessage(message) {
            Swal.fire({
                title: 'Oops!',
                text: message != undefined && message.length <= 0 ? 'Oops Something went wrong. Contact support for assistance' : message,
                icon: 'error',
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            });
            this.StopLoaders();

        },
    }
}
</script>