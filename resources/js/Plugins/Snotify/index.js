/**
 * |--------------------------------------------------------------------------
 * | Snotify config
 * |--------------------------------------------------------------------------
 */
import Vue from 'vue';
import Snotify, { SnotifyPosition } from 'vue-snotify';
import "vue-snotify/styles/material.css";

Vue.use(Snotify, {
    toast: {
        timeout: 3000,
        position: SnotifyPosition.rightTop
    }
});
