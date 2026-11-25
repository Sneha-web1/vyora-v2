{{-- Global "active field" highlight (subtle). Included in every layout. --}}
<style>
    :root { --focus-color: #332c6d; --focus-ring: rgba(51, 44, 109, .14); }

    /* active text box / textarea / dropdown: purple border + soft ring, background untouched */
    input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="file"]):not([type="submit"]):not([type="button"]):not([type="hidden"]):not(.select2-search__field):focus,
    textarea:focus,
    select:focus,
    .form-control:focus,
    .form-select:focus,
    .select2-container--focus .select2-selection,
    .select2-container--open .select2-selection,
    .note-editor.note-frame.note-focus,
    .bootstrap-tagsinput.focus {
        border-color: var(--focus-color) !important;
        box-shadow: 0 0 0 3px var(--focus-ring) !important;
        outline: 0 !important;
    }

    /* select2 search box inside the open dropdown: plain, no ring */
    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: var(--focus-color) !important;
        box-shadow: none !important;
        outline: 0 !important;
    }

    /* icon box next to the active input: only icon + border take the theme colour */
    .input-group:focus-within > .input-group-text {
        border-color: var(--focus-color) !important;
        color: var(--focus-color) !important;
    }
    .input-group:focus-within > .input-group-text i { color: var(--focus-color) !important; }

    /* label of the active field */
    :is(.mb-3, .form-group, [class*="col-"]):focus-within > :is(.form-label, label) {
        color: var(--focus-color);
    }

    /* checkbox / radio / file */
    .form-check-input:focus,
    input[type="checkbox"]:focus,
    input[type="radio"]:focus,
    input[type="file"]:focus {
        box-shadow: 0 0 0 3px var(--focus-ring) !important;
        outline: 0 !important;
    }
</style>
