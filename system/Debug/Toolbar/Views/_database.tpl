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
            <td class="debug-bar-alignRight"><strong>{trace-file}</strong></td>
        </tr>
        <tr class="muted debug-bar-ndisplay" id="{qid}-trace">
            <td></td>
            <td colspan="2">
            {trace}
                {index}<strong>{file}</strong><br/>
                {function}<br/><br/>
            {/trace}
            </td>
        </tr>
    {/queries}
    </tbody>
</table>
