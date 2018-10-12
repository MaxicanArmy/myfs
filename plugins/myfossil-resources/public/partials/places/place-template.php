<?php

function myfossil_places_template()
{
?>
    <script id="tpl-places" type="text/x-handlebars-template">
    {{#each places}}
        <div class="place place-{{ type }}"
                data-place-state="{{ state }}"
                data-place-type="{{ type }}">
            <div class="place-icon">
                <i class="fa fa-map-marker"></i>
            </div>
            <div class="place-body">
                <span>{{ type }}</span>
                <h5>{{ name }}</h5>
                <p>{{ description }}</p>
            </div>
        </div>
    {{/each}}
    </script>

    <?php
}
