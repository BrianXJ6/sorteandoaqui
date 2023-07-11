<template>
    <div>
        <Head :title="title">
            <meta name="DC.title" :content="title" />
            <meta property="og:title" :content="title" />
            <meta name="abstract" :content="description" />
            <meta name="description" :content="description" />
            <meta property="og:description" :content="description" />
            <meta name="keywords" :content="keywords" />
            <meta property="og:url" :content="$page.url" />
            <meta property="og:image" content="/statics/images/cover.png" />
        </Head>
        <div class="container">
            <h1 v-html="label" />
            <hr class="mt-0" />
            <div class="card shadow">
                <div class="card-header text-bg-primary fs-3" v-html="`Fale conosco`" />
                <div class="card-body">
                    <form @submit.prevent="sendContactForm">
                        <div class="form-floating mb-3">
                            <input
                                id="contact-data-name"
                                class="form-control"
                                type="text"
                                placeholder="Nome e sobrenome"
                                maxlength="100"
                                pattern="^[a-zA-Z]{1,}[a-zA-Zà-úÀ-Ú]{1,}\.?\s[a-zA-Zà-úÀ-Ú\.\s]{1,}"
                                autofocus
                                required
                                v-model="contact.data.name"
                            />
                            <label for="contact-data-name">Nome e sobrenome</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input
                                id="contact-data-email"
                                class="form-control"
                                type="email"
                                placeholder="E-mail"
                                maxlength="100"
                                required
                                v-model="contact.data.email"
                            />
                            <label for="contact-data-email">E-mail</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input
                                id="contact-data-phone"
                                class="form-control"
                                type="tel"
                                placeholder="Telefone"
                                minlength="11"
                                maxlength="15"
                                pattern="\(?\d{2}\)?\s?\d{5}-?\d{4}"
                                required
                                v-maska
                                data-maska="(##) #####-####"
                                v-model="contact.data.phone"
                            />
                            <label for="contact-data-phone">Telefone</label>
                        </div>
                        <div class="form-floating mb-3">
                            <select
                                id="contact-data-subject"
                                class="form-select"
                                required
                                v-model="contact.data.subject"
                            >
                                <option value selected>Selecione</option>
                                <option value="Dúvidas">Dúvidas</option>
                                <option value="Elogios">Elogios</option>
                                <option value="Criticas">Criticas</option>
                                <option value="Sugestões">Sugestões</option>
                            </select>
                            <label for="contact-data-subject" v-html="`Assunto`" />
                        </div>
                        <div class="form-floating mb-3">
                            <textarea
                                id="contact-data-message"
                                class="form-control"
                                placeholder="Mensagem"
                                minlength="20"
                                :maxlength="contact.messageMaxLength"
                                required
                                v-model="contact.data.message"
                            ></textarea>
                            <label for="contact-data-message">
                                Mensagem
                                <sup
                                    class="text-muted ms-1"
                                >{{ contact.messageMaxLength }}/{{ contact.messageMaxLength - contact.data.message.length }}</sup>
                            </label>
                        </div>
                        <div
                            v-if="recaptchaEnabled"
                            id="g-recaptcha"
                            class="g-recaptcha"
                            :data-sitekey="googleRecaptchaPublicKey"
                        />
                        <div class="d-grid gap-2 d-flex justify-content-end align-items-center">
                            <div v-if="contact.loader" class="spinner-border" />
                            <button
                                class="btn btn-primary"
                                type="submit"
                                :disabled="contact.loader"
                                v-html="`Enviar`"
                            />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Head } from '@inertiajs/vue2';
import GoogleRecaptcha from './../../Mixins/GoogleRecaptcha.vue';
export default {
    components: { Head },
    mixins: [GoogleRecaptcha],

    props: {
        title: String,
        label: String,
        description: String,
        keywords: String,
    },

    data: () => {
        return {
            contact: {
                loader: false,
                messageMaxLength: 1024,
                data: {
                    name: '',
                    email: '',
                    phone: '',
                    subject: '',
                    message: '',
                    recaptcha: '',
                }
            }
        };
    },

    methods: {
        async sendContactForm(el) {
            this.contact.loader = true;
            try {
                // Validate recaptcha
                this.contact.data.recaptcha = this.validateRecaptcha();

                // Request
                const response = await axios.post(route('api.open.web-contact'), this.$prepareData(this.contact.data));
                this.$clearForm(el.target, this.contact.data);
                this.$snotify.success(response.data.message);
            }
            catch (error) { this.$showErrors(error) }
            finally {
                this.contact.loader = false;
                this.recaptchaReset();
            }
        },
    },
}
</script>

<style scoped>
#contact-data-message {
    height: 150px;
}
</style>
