<template>
    <div class="container">
        <Head :title="title" />
        <h1 v-html="label" />
        <hr />
        <div class="card mb-5 shadow">
            <div class="card-header text-bg-primary" v-html="`Dados pessoais`" />
            <div class="card-body">
                <form @submit.prevent="personalDataForm">
                    <div class="form-floating mb-3">
                        <input
                            id="personalData-form-name"
                            class="form-control"
                            type="text"
                            placeholder="Nome"
                            maxlength="100"
                            pattern="^[a-zA-Z]{1,}[a-zA-Zà-úÀ-Ú]{1,}\.?\s[a-zA-Zà-úÀ-Ú\.\s]{1,}"
                            v-model="personalData.form.name"
                        />
                        <label for="personalData-form-name">Nome</label>
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
export default {
    components: { Head },
    props: { title: String, label: String },

    data: (vm) => {
        return {
            personalData: {
                loader: false,
                form: {
                    name: vm.$page.props.admin.name,
                    email: vm.$page.props.admin.email,
                    phone: vm.$page.props.admin.phone,
                }
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
        admin: (vm) => vm.$page.props.admin,
    },

    methods: {
        async personalDataForm() {
            this.personalData.loader = true;
            try {
                const response = await axios.put(route('api.admin.acc.update-personal-data'), this.$prepareData(this.personalData.form));
                const { message, admin } = response.data;
                this.$page.props.admin = admin;
                this.$snotify.success(message);
            }
            catch (error) { this.$showErrors(error); }
            finally { this.personalData.loader = false; }
        },
        async accessDataForm(el) {
            this.accessData.loader = true;
            try {
                this.confirmPasswords();
                const response = await axios.put(route('api.admin.acc.update-access-data'), this.$prepareData(this.accessData.form));
                this.$clearForm(el.target, this.accessData.form);
                this.$snotify.success(response.data.message);
            }
            catch (error) { this.$showErrors(error); }
            finally { this.accessData.loader = false; }
        },

        confirmPasswords() {
            if (this.accessData.form.password !== this.accessData.form.password_confirmation)
                throw { response: { data: { errors: { password: ['O campo senha de confirmação não confere.'] } } } };
        },
    },
}
</script>
