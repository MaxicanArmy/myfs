<?php

function myfossil_events_template()
{
    ?>
    <script id="tpl-events" type="text/x-handlebars-template">
    {{#each events}}
        <div class="event event-{{ type }}"
                data-event-state="{{ State }}"
                data-event-type="{{ type }}"
                data-event-date="{{ month_year }}">
            <div class="event-icon">
                <i class="fa fa-map-marker"></i>
            </div>
            <div class="event-body">
                <span>{{ type }}</span>
                <h5>{{ title }}</h5>
                <p>{{ content }}</p>
            </div>
        </div>
    {{/each}}
    </script>

    <?php
}
