// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Front-end behaviour for block_notices: the Swiper carousel on the block,
 * and the "Added/Updated X ago" labels that are computed client-side.
 *
 * @module     block_notices/block_notices
 * @copyright  2025 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import {get_strings as getStrings} from 'core/str';

const SELECTOR = '[data-block-notices-updatedescription]';
const REFRESH_INTERVAL_MS = 60 * 1000;
const COMPONENT = 'block_notices';

const STRING_KEYS = [
    'updatedescription_prefix_added',
    'updatedescription_prefix_updated',
    'updatedescription_justnow',
    'updatedescription_afewminutesago',
    'updatedescription_nminutesago',
    'updatedescription_aboutahourago',
    'updatedescription_nhoursago',
    'updatedescription_yesterday',
    'updatedescription_ndaysago',
    'updatedescription_aboutaweekago',
    'updatedescription_nweeksago',
    'updatedescription_aboutamonthago',
    'updatedescription_nmonthsago',
    'updatedescription_aboutayearago',
    'updatedescription_overayearago',
];

let stringsPromise = null;
const loadStrings = () => {
    if (stringsPromise === null) {
        // Fetch with no $a so the {$a->prefix}/{$a->amount} placeholders remain
        // intact in the returned template, ready for client-side substitution.
        stringsPromise = getStrings(STRING_KEYS.map(key => ({key, component: COMPONENT})))
            .then(values => {
                const map = {};
                STRING_KEYS.forEach((k, i) => {
                    map[k] = values[i];
                });
                return map;
            });
    }
    return stringsPromise;
};

const SECONDS_PER_MINUTE = 60;
const SECONDS_PER_HOUR = 60 * 60;
const SECONDS_PER_DAY = 24 * 60 * 60;
const SECONDS_PER_WEEK = 7 * SECONDS_PER_DAY;

/**
 * Pick the right time-ago template and (when applicable) numeric amount.
 *
 * @param {number} delta Seconds since timemodified.
 * @returns {{templateKey: string, amount: (number|null)}}
 */
const pickTemplate = (delta) => {
    if (delta < SECONDS_PER_MINUTE) {
        return {templateKey: 'updatedescription_justnow', amount: null};
    }
    if (delta < 10 * SECONDS_PER_MINUTE) {
        return {templateKey: 'updatedescription_afewminutesago', amount: null};
    }
    if (delta < SECONDS_PER_HOUR) {
        return {templateKey: 'updatedescription_nminutesago', amount: Math.floor(delta / SECONDS_PER_MINUTE)};
    }
    if (delta < 90 * SECONDS_PER_MINUTE) {
        return {templateKey: 'updatedescription_aboutahourago', amount: null};
    }
    if (delta < SECONDS_PER_DAY) {
        return {templateKey: 'updatedescription_nhoursago', amount: Math.round(delta / SECONDS_PER_HOUR)};
    }
    if (delta < 2 * SECONDS_PER_DAY) {
        return {templateKey: 'updatedescription_yesterday', amount: null};
    }
    if (delta < SECONDS_PER_WEEK) {
        return {templateKey: 'updatedescription_ndaysago', amount: Math.floor(delta / SECONDS_PER_DAY)};
    }
    if (delta < 2 * SECONDS_PER_WEEK) {
        return {templateKey: 'updatedescription_aboutaweekago', amount: null};
    }
    if (delta < 30 * SECONDS_PER_DAY) {
        return {templateKey: 'updatedescription_nweeksago', amount: Math.floor(delta / SECONDS_PER_WEEK)};
    }
    if (delta < 60 * SECONDS_PER_DAY) {
        return {templateKey: 'updatedescription_aboutamonthago', amount: null};
    }
    if (delta < 365 * SECONDS_PER_DAY) {
        return {templateKey: 'updatedescription_nmonthsago', amount: Math.floor(delta / (30 * SECONDS_PER_DAY))};
    }
    if (delta < 730 * SECONDS_PER_DAY) {
        return {templateKey: 'updatedescription_aboutayearago', amount: null};
    }
    return {templateKey: 'updatedescription_overayearago', amount: null};
};

/**
 * Build the label string for a single notice given the prefetched lang strings.
 *
 * @param {Object} strings Map of string key -> template (with {$a->prefix}/{$a->amount} placeholders).
 * @param {number} timecreated Unix timestamp (seconds).
 * @param {number} timemodified Unix timestamp (seconds).
 * @param {number} now Unix timestamp (seconds).
 * @returns {string}
 */
const buildLabel = (strings, timecreated, timemodified, now) => {
    const prefix = (timecreated === timemodified)
        ? strings.updatedescription_prefix_added
        : strings.updatedescription_prefix_updated;
    const delta = Math.max(0, now - timemodified);
    const {templateKey, amount} = pickTemplate(delta);
    let label = strings[templateKey].replace('{$a->prefix}', prefix);
    if (amount !== null) {
        label = label.replace('{$a->amount}', amount);
    }
    return label;
};

const updateAllTimestamps = () => {
    const elements = document.querySelectorAll(SELECTOR);
    if (elements.length === 0) {
        return;
    }
    loadStrings().then(strings => {
        const now = Math.floor(Date.now() / 1000);
        elements.forEach(el => {
            const tc = parseInt(el.dataset.timecreated, 10);
            const tm = parseInt(el.dataset.timemodified, 10);
            if (Number.isNaN(tc) || Number.isNaN(tm)) {
                return;
            }
            el.textContent = buildLabel(strings, tc, tm, now);
        });
        return null;
    }).catch(() => {
        // Failed to load strings; leave the hourglass placeholder visible.
    });
};

export const init = () => {
    updateAllTimestamps();
    setInterval(updateAllTimestamps, REFRESH_INTERVAL_MS);

    // Swiper is only loaded on the block itself, not on the manage page.
    if (!document.querySelector('#stream-dashboard-notices .swiper')) {
        return;
    }

    /* global Swiper */
    /* eslint no-undef: "error" */
    let swiper = new Swiper('.swiper', {
        direction: 'horizontal',
        spaceBetween: 30,
        autoplay: {
            delay: 5000,
            pauseOnMouseEnter: true,
            disableOnInteraction: true,
        },
        loop: true,

        // We need pagination
        pagination: {
            el: '.swiper-pagination',
        },

        // Navigation arrows
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });

    $('.swiper-button-playpause').on('click', function() {
        if (swiper.autoplay.running) {
            swiper.autoplay.stop();
            $(this).removeClass('fa-pause').addClass('fa-play');
        } else {
            swiper.autoplay.start();
            $(this).removeClass('fa-play').addClass('fa-pause');
        }
    });
};
