{*
  variables that are available:
  - {$items}: contains all agenda
  - {$wholeday}: whole day agenda bool (yes/no)
*}

{*{$items|dump}*}

<article class="full blogDetail">
    <div class="blogDetailTitle">
        <h3>{$lblUpcomingAgendaItems|ucfirst}</h3>
    </div>
    <div class="centered">

        <p><a href="?timestamp={$timestamp}&amp;view=day">{$lblToday|ucfirst}</a></p>

        <p><a href="?timestamp={$timestamp}&amp;view=month">{$lblMonth|ucfirst}</a></p>

        <p><a href="?timestamp={$timestamp}&amp;view=week">{$lblWeek|ucfirst}</a></p>

        <p><a href="?timestamp={$timestamp}&amp;view=day">{$lblDay|ucfirst}</a></p>

        <p><a href="{$prevUrl}">{$lblPrevious|ucfirst} {$viewLabel|lowercase}</a></p>

        <p><a href="{$nextUrl}">{$lblNext|ucfirst} {$viewLabel|lowercase}</a></p>

        <p>{$viewLabel|ucfirst}: {$viewTitle|ucfirst}</p>

        <div id="data-agenda">
            {option:items}
                <div class="agendaItems">
                    <table class="responsive">
                        <thead>
                        <tr>
                            <th>{$lblTitle|ucfirst}</th>
                            <th>{$lblCategory|ucfirst}</th>
                            <th>{$lblDate|ucfirst}/{$lblTime|ucfirst}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {iteration:items}
                            <tr>
                                <td data-title="title">
                                    <a href="{$items.full_url}?begindate={$items.ts_begin_date}&amp;enddate={$items.ts_end_date}"><b>{$items.title}</b></a>
                                </td>
                                <td data-title="category">
                                    <a href="{$items.category_full_url}"><span class="category" style="color: {$items.category_color}">{$items.category_title}</span></a>
                                </td>
                                <td data-title="datetime">
                                    <time itemprop="date" datetime="{$items.begin_date|date:'Y-m-d\TH:i:s'}">{$items.begin_date|date:{$dateFormatLong}:{$LANGUAGE}}</time>
                                    <time itemprop="time" datetime="{$items.begin_date|date:'Y-m-d\TH:i:s'}">{$items.begin_date|date:{$timeFormat}:{$LANGUAGE}}</time>

                                    {option:!items.whole_day}
                                        -
                                    {option:items.different_end_date}
                                        <time itemprop="date" datetime="{$items.end_date|date:'Y-m-d\TH:i:s'}">{$items.end_date|date:{$dateFormatLong}:{$LANGUAGE}}</time>
                                    {/option:items.different_end_date}
                                        <time itemprop="time" datetime="{$items.end_date|date:'Y-m-d\TH:i:s'}">{$items.end_date|date:{$timeFormat}:{$LANGUAGE}}</time>
                                    {/option:!items.whole_day}
                                </td>
                            </tr>
                        {/iteration:items}
                        </tbody>
                    </table>
                </div>
            {/option:items}
            {option:!items}
                <p class="date">{$lblNoAgenda|ucfirst}.</p>
            {/option:!items}
        </div>
    </div>
</article>

{include:core/layout/templates/pagination.tpl}