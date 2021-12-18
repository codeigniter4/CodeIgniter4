<table>
    <thead>
        <tr>
            <th class="debug-bar-width6r">Time</th>
            <th>Query String</th>
        </tr>
    </thead>
    <tbody>
    {queries}
        <tr class="{class}" title="{hover}" data-toggle="{qid}-trace">
            <td class="narrow">{duration}</td>
            <td>{! sql !}</td>
            <td style="text-align: right">{trace-file}:<strong>{trace-line}</strong></td>
        </tr>
        <tr class="muted" id="{qid}-trace" style="display:none">
            <td></td>
            <td colspan="2">
            {trace}
                {file}:<strong>{line}</strong><br/>
            {/trace}
            </td>
        </tr>
    {/queries}
    </tbody>
</table>
