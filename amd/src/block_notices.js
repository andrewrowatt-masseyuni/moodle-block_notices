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
import Ajax from 'core/ajax';

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

const DWELL_MS = 1000;
const FLUSH_DEBOUNCE_MS = 1500;

/**
 * Track which slide of the carousel the user has dwelled on while the block is
 * in viewport, and POST the ack ids in debounced batches.
 *
 * @param {Element} root The #stream-dashboard-notices container.
 * @param {Object} swiper The Swiper instance for the carousel.
 */
const initReadTracking = (root, swiper) => {
    const courseid = parseInt(root.dataset.courseid, 10);
    if (!Number.isFinite(courseid)) {
        return;
    }
    // The server-side external function is the source of truth for who can record a read;
    // for guests/anonymous it returns an empty acknowledged list without writing anything.

    const acked = new Set();
    const pending = new Set();
    let inViewport = false;
    let dwellTimer = null;
    let flushTimer = null;
    let flushInflight = false;

    const currentSlideId = () => {
        const active = root.querySelector('.swiper-slide-active[data-notice-id]');
        if (!active) {
            return null;
        }
        const id = parseInt(active.dataset.noticeId, 10);
        return Number.isFinite(id) ? id : null;
    };

    const scheduleFlush = () => {
        if (flushTimer !== null) {
            window.clearTimeout(flushTimer);
        }
        flushTimer = window.setTimeout(() => {
            flushTimer = null;
            flush();
        }, FLUSH_DEBOUNCE_MS);
    };

    const flush = () => {
        if (flushInflight || pending.size === 0) {
            return;
        }
        const noticeids = [...pending];
        flushInflight = true;
        const [request] = Ajax.call([{
            methodname: 'block_notices_mark_read',
            args: {courseid, noticeids},
        }]);
        request.then(response => {
            (response.acknowledged || []).forEach(id => {
                pending.delete(id);
                acked.add(id);
            });
            return null;
        }, () => {
            // Leave items in pending; the next scheduleFlush() will retry.
            return null;
        }).then(() => {
            flushInflight = false;
            if (pending.size > 0) {
                scheduleFlush();
            }
            return null;
        }).catch(() => {
            flushInflight = false;
        });
    };

    const cancelDwell = () => {
        if (dwellTimer !== null) {
            window.clearTimeout(dwellTimer);
            dwellTimer = null;
        }
    };

    const evaluate = () => {
        cancelDwell();
        if (!inViewport) {
            return;
        }
        const id = currentSlideId();
        if (id === null || acked.has(id) || pending.has(id)) {
            return;
        }
        dwellTimer = window.setTimeout(() => {
            dwellTimer = null;
            // Reconfirm state before acking.
            if (!inViewport) {
                return;
            }
            if (currentSlideId() !== id) {
                return;
            }
            if (acked.has(id)) {
                return;
            }
            pending.add(id);
            scheduleFlush();
        }, DWELL_MS);
    };

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                inViewport = entry.isIntersecting;
                evaluate();
            });
        }, {threshold: 0.5});
        observer.observe(root);
    } else {
        inViewport = true;
    }

    swiper.on('slideChangeTransitionEnd', evaluate);
    swiper.on('init', evaluate);
    // Evaluate immediately for the first slide (Swiper is already initialised).
    evaluate();
};

export const init = () => {
    updateAllTimestamps();
    setInterval(updateAllTimestamps, REFRESH_INTERVAL_MS);

    // Swiper is only loaded on the block itself, not on the manage page.
    const root = document.querySelector('#stream-dashboard-notices');
    if (!root || !root.querySelector('.swiper')) {
        return;
    }

    /* global Swiper */
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

    initReadTracking(root, swiper);
};
