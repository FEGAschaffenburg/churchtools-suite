<?php

$search_value = $args["search_term"];
$custom_class = $args["custom_class"];
?>

<div data-view="search-classic" class="<?php echo esc_attr( $custom_class ); ?>">
    <form class="cts-search-form" method="get" role="search" aria-label="Veranstaltungen durchsuchen">
        <label class="screen-reader-text" for="cts-eventsearch-keyword">Suchwort eingeben</label>

        <div class="cts-search-form__controls">
            <input
                class="cts-eventsearch-input"
                type="search"
                id="cts-eventsearch-keyword"
                name="event-search"
                value="<?php echo esc_attr($search_value); ?>"
                placeholder="Suche nach Veranstaltungen"
                aria-label="Bitte Suchwort für Suche nach Veranstaltung eingeben." />

            <button class="cts-eventsearch-button" type="submit">
                <span class="dashicons dashicons-search" aria-hidden="true"></span>
                <span>Suchen</span>
            </button>
        </div>
    </form>
</div>