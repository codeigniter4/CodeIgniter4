{ if $logs == [] }
<p>Nothing was logged. If you were expecting logged items, ensure that LoggerConfig file has the correct threshold set.</p>
{ else }
<table>
    <thead>
        <tr>
            <th>Severity</th>
            <th>Message</th>
        </tr>
    </thead>
    <tbody>
    {logs}
        <tr>
            <td>{level}</td>
            <td>{msg}</td>
        </tr>
    {/logs}
    </tbody>
</table>
{ endif }
