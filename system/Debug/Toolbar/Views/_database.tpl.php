<table>
    <thead>
        <tr>
            <th style="width: 6rem;">Time</th>
            <th>Query String</th>
        </tr>
    </thead>
    <tbody>
    {queries}
        <tr>
            <td class="narrow">{duration}</td>
            <td>{! sql !}</td>
        </tr>
    {/queries}
    </tbody>
</table>
