<template>
  <Layout>
    <PageHeader :title="title" :items="items" />
    <b-card class="mb-4">
      <b-card-body>
        <b-form @submit.prevent="updateSettings">
          <b-form-group label="Name" :state="errors.name ? false : null">
            <b-form-input v-model="settings.name" :state="errors.name ? false : null"></b-form-input>
            <b-form-invalid-feedback v-if="errors.name">{{ errors.name[0] }}</b-form-invalid-feedback>
          </b-form-group>

          <b-form-group label="Email" :state="errors.email ? false : null">
            <b-form-input v-model="settings.email" type="email" :state="errors.email ? false : null"></b-form-input>
            <b-form-invalid-feedback v-if="errors.email">{{ errors.email[0] }}</b-form-invalid-feedback>
          </b-form-group>

          <b-form-group label="Phone" :state="errors.phone ? false : null">
            <b-form-input v-model="settings.phone" :state="errors.phone ? false : null"></b-form-input>
            <b-form-invalid-feedback v-if="errors.phone">{{ errors.phone[0] }}</b-form-invalid-feedback>
          </b-form-group>

          <b-form-group label="Facebook Link" :state="errors.facebook_link ? false : null">
            <b-form-input v-model="settings.facebook_link" :state="errors.facebook_link ? false : null"></b-form-input>
            <b-form-invalid-feedback v-if="errors.facebook_link">{{ errors.facebook_link[0] }}</b-form-invalid-feedback>
          </b-form-group>

          <b-form-group label="Twitter Link" :state="errors.twitter_link ? false : null">
            <b-form-input v-model="settings.twitter_link" :state="errors.twitter_link ? false : null"></b-form-input>
            <b-form-invalid-feedback v-if="errors.twitter_link">{{ errors.twitter_link[0] }}</b-form-invalid-feedback>
          </b-form-group>

          <b-form-group label="Instagram Link" :state="errors.instagram_link ? false : null">
            <b-form-input v-model="settings.instagram_link" :state="errors.instagram_link ? false : null"></b-form-input>
            <b-form-invalid-feedback v-if="errors.instagram_link">{{ errors.instagram_link[0] }}</b-form-invalid-feedback>
          </b-form-group>

          <b-form-group label="YouTube Link" :state="errors.youtube_link ? false : null">
            <b-form-input v-model="settings.youtube_link" :state="errors.youtube_link ? false : null"></b-form-input>
            <b-form-invalid-feedback v-if="errors.youtube_link">{{ errors.youtube_link[0] }}</b-form-invalid-feedback>
          </b-form-group>

          <b-form-group label="Address" :state="errors.address ? false : null">
            <b-form-textarea v-model="settings.address" rows="3" :state="errors.address ? false : null"></b-form-textarea>
            <b-form-invalid-feedback v-if="errors.address">{{ errors.address[0] }}</b-form-invalid-feedback>
          </b-form-group>

          <!-- <b-form-group label="Logo" :state="errors.logo ? false : null">
            <b-form-file @change="onLogoChange" :state="errors.logo ? false : null"></b-form-file>
            <b-form-invalid-feedback v-if="errors.logo">{{ errors.logo[0] }}</b-form-invalid-feedback>
          </b-form-group> -->

          <b-form-group label="Advance Payment (%)" :state="errors.advance_payment_taken_in_percentage ? false : null">
            <b-form-input
              v-model="settings.advance_payment_taken_in_percentage"
              type="number"
              min="0"
              max="100"
              :state="errors.advance_payment_taken_in_percentage ? false : null"
            ></b-form-input>
            <b-form-invalid-feedback v-if="errors.advance_payment_taken_in_percentage">
              {{ errors.advance_payment_taken_in_percentage[0] }}
            </b-form-invalid-feedback>
          </b-form-group>

          <b-button type="submit" variant="primary">Update Settings</b-button>
        </b-form>
      </b-card-body>
    </b-card>
  </Layout>
</template>
<script>
import Layout from "@/layouts/main.vue";
import PageHeader from "@/components/page-header.vue";

export default {
  components: {
    Layout,
    PageHeader,
  },
  data() {
    return {
      title: "Edit Settings",
      settings: {
        name: '',
        email: '',
        phone: '',
        facebook_link: '',
        twitter_link: '',
        instagram_link: '',
        youtube_link: '',
        address: '',
        logo: null,
        advance_payment_taken_in_percentage: null,
      },
      logoFile: null,
      errors: {},
      items: [
        { text: "Dashboard", href: "/" },
        { text: "Settings", active: true },
      ],
    };
  },
  created() {
    this.fetchSettings();
  },
  computed: {
    settingId() {
      return this.$route?.params?.id;
    },
  },
  methods: {
    fetchSettings() {
      this.$http.get('/settings/'+this.settingId).then(response => {
        this.settings = response.data;
      });
    },
    onLogoChange(event) {
      this.logoFile = event.target.files[0];
    },
    updateSettings() {
      

      this.$http.put('/settings/'+this.settingId,{
        name: this.settings.name,
        email: this.settings.email,
        phone: this.settings.phone,
        facebook_link: this.settings.facebook_link,
        twitter_link: this.settings.twitter_link,
        instagram_link: this.settings.instagram_link,
        youtube_link: this.settings.youtube_link,
        address: this.settings.address,
        logo: this.settings.logo,
        advance_payment_taken_in_percentage: this.settings.advance_payment_taken_in_percentage
      })
        .then(() => {
          this.$router.push({ name: 'Settings' });
        })
        .catch(error => {
          if (error.response && error.response.data.errors) {
            this.errors = error.response.data.errors;
          }
        });
    },
  },
};
</script>
