<template>
    <div class="container">
        <Head :title="title" />
        <h1 v-html="label" />
        <hr />
        <div v-if="!user.email_verified_at" class="alert small alert-danger shadow">
            <h4 class="alert-heading">
                <i class="bi bi-exclamation-triangle-fill"></i> E-mail não verificado!
            </h4>
            <hr class="mt-0" />
            <p>
                Seu e-mail ainda não foi verificado!
                Por segurança precisamos validar se seu endereço de e-mail é válido, algumas funcionalidades só estão disponíveis para quem realizou essa etapa.
            </p>
            <p>Clique no botão abaixo para receber um e-mail com um link de verificação.</p>
            <button
                type="button"
                class="btn btn-danger shadow-sm"
                :disabled="emailVerify.loader || emailVerify.timeout > 0"
                @click="resendEmailVerify"
                style="width:262px"
            >
                <div v-if="emailVerify.loader" class="spinner-border spinner-border-sm"></div>
                <span v-else-if="emailVerify.timeout" v-html="emailVerify.timeout"></span>
                <span v-else v-html="emailVerify.btnLabel" />
            </button>
        </div>
        <div class="card mb-5 shadow">
            <div class="card-header text-bg-primary" v-html="`Dados pessoais`" />
            <div class="card-body">
                <form @submit.prevent="personalDataForm">
                    <div class="form-floating mb-3">
                        <input
                            id="personalData-form-cpf"
                            class="form-control"
                            type="tel"
                            placeholder="CPF"
                            pattern="\d{3}\.?\d{3}\.?\d{3}-?\d{2}"
                            minlength="11"
                            maxlength="14"
                            data-maska="###.###.###-##"
                            v-maska
                            autofocus
                            :disabled="$page.props.user.cpf"
                            :required="!$page.props.user.cpf"
                            v-model="personalData.form.cpf"
                        />
                        <label for="personalData-form-cpf">CPF</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input
                            id="personalData-form-nick"
                            class="form-control"
                            type="text"
                            placeholder="Apelido"
                            minlength="3"
                            maxlength="10"
                            v-model="personalData.form.nick"
                        />
                        <label for="personalData-form-nick">Apelido</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input
                            id="personalData-form-name"
                            class="form-control"
                            type="text"
                            placeholder="Nome e sobrenome"
                            maxlength="100"
                            pattern="^[a-zA-Z]{1,}[a-zA-Zà-úÀ-Ú]{1,}\.?\s[a-zA-Zà-úÀ-Ú\.\s]{1,}"
                            v-model="personalData.form.name"
                        />
                        <label for="personalData-form-name">Nome e sobrenome</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input
                            id="personalData-form-email"
                            class="form-control"
                            type="email"
                            placeholder="E-mail"
                            maxlength="100"
                            v-model="personalData.form.email"
                        />
                        <label for="personalData-form-email">E-mail</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input
                            id="personalData-form-phone"
                            class="form-control"
                            type="tel"
                            placeholder="Telefone"
                            minlength="11"
                            maxlength="15"
                            pattern="\(?\d{2}\)?\s?\d{5}-?\d{4}"
                            data-maska="(##) #####-####"
                            v-maska
                            v-model="personalData.form.phone"
                        />
                        <label for="personalData-form-phone">Telefone</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input
                            id="personalData-form-referral_code"
                            class="form-control"
                            type="text"
                            placeholder="Cod. de referência"
                            maxlength="10"
                            minlength="10"
                            pattern="\w{10}"
                            v-model="personalData.form.referral_code"
                        />
                        <label for="personalData-form-referral_code">Cod. de referência</label>
                    </div>
                    <div class="d-grid gap-2 d-flex justify-content-end align-items-center">
                        <div v-if="personalData.loader" class="spinner-border" />
                        <button
                            class="btn btn-primary"
                            type="submit"
                            :disabled="personalData.loader"
                            v-html="`Atualizar`"
                        />
                    </div>
                </form>
            </div>
        </div>
        <div class="card shadow">
            <div class="card-header text-bg-primary" v-html="`Acesso`" />
            <div class="card-body">
                <form @submit.prevent="accessDataForm">
                    <div class="form-floating mb-3">
                        <input
                            id="accessData-form-current_password"
                            class="form-control"
                            type="password"
                            placeholder="Senha atual"
                            v-model="accessData.form.current_password"
                        />
                        <label for="accessData-form-current_password">Senha atual</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input
                            id="accessData-form-password"
                            class="form-control"
                            type="password"
                            placeholder="Nova senha"
                            minlength="8"
                            v-model="accessData.form.password"
                        />
                        <label for="accessData-form-password">Nova senha</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input
                            id="accessData-form-password_confirmation"
                            class="form-control"
                            type="password"
                            placeholder="Repetir senha"
                            minlength="8"
                            v-model="accessData.form.password_confirmation"
                        />
                        <label for="accessData-form-password_confirmation">Repetir senha</label>
                    </div>
                    <div class="d-grid gap-2 d-flex justify-content-end align-items-center">
                        <div v-if="accessData.loader" class="spinner-border" />
                        <button
                            class="btn btn-primary"
                            type="submit"
                            :disabled="accessData.loader"
                            v-html="`Atualizar`"
                        />
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { Head } from '@inertiajs/vue2';
import moment from 'moment';
export default {
    components: { Head },
    props: { title: String, label: String },

    data: (vm) => {
        return {
            emailVerify: {
                loader: false,
                btnLabel: 'Enviar e-mail de verificação',
                timeout: 0,
            },
            personalData: {
                loader: false,
                form: {
                    cpf: vm.$page.props.user.cpf,
                    nick: vm.$page.props.user.nick,
                    name: vm.$page.props.user.name,
                    email: vm.$page.props.user.email,
                    phone: vm.$page.props.user.phone,
                    referral_code: vm.$page.props.user.referral_code,
                },
            },
            accessData: {
                loader: false,
                form: {
                    current_password: '',
                    password: '',
                    password_confirmation: '',
                },
            },
        };
    },

    computed: {
        user: (vm) => vm.$page.props.user,
    },

    methods: {
        countDown() {
            let time = sessionStorage.getItem('emailRequest') ? moment(new Date(sessionStorage.getItem('emailRequest'))) : null;
            time = time ? parseInt((time.diff(moment()) / 1000)) : 0;

            if (time <= 0) {
                sessionStorage.removeItem('emailRequest');
                time = 0;
            }

            this.emailVerify.timeout = time;
            const countDown = setInterval(() => this.emailVerify.timeout--, 1000);
            setTimeout(() => clearInterval(countDown), this.emailVerify.timeout * 1100);
        },

        async resendEmailVerify() {
            this.emailVerify.loader = true;
            try {
                const response = await axios.post(route('web.action.user.auth.email.verify.resend'));
                this.$snotify.success(response.data.message);
                sessionStorage.setItem('emailRequest', moment().add(30, 'seconds'));
                this.countDown();
            }
            catch (error) { this.$showErrors(error); }
            finally { this.emailVerify.loader = false; }
        },

        async personalDataForm() {
            this.personalData.loader = true;
            try {
                const response = await axios.put(route('api.user.acc.update-personal-data'), this.$prepareData(this.personalData.form));
                const { message, user } = response.data;
                this.$page.props.user = user;
                this.$snotify.success(message);
            }
            catch (error) { this.$showErrors(error); }
            finally { this.personalData.loader = false; }
        },

        async accessDataForm(el) {
            this.accessData.loader = true;
            try {
                this.confirmPasswords();
                const response = await axios.put(route('api.user.acc.update-access-data'), this.$prepareData(this.accessData.form));
                this.$clearForm(el.target, this.accessData.form);
                this.$snotify.success(response.data.message);
            }
            catch (error) { this.$showErrors(error); }
            finally { this.accessData.loader = false; }
        },

        confirmPasswords() {
            if (this.accessData.form.password !== this.accessData.form.password_confirmation)
                throw { response: { data: { errors: { password: ['O campo senha de confirmação não confere.'] } } } };
        }
    },

    beforeMount() {
        this.countDown();
    }
}
</script>
