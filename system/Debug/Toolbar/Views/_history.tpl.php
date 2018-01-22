<table>
    <thead>
        <tr>
            <th>Action</th>
            <th>Datetime</th>
            <th>Status</th>
            <th>Method</th>
            <th>URL</th>
            <th>Content-Type</th>
            <th>Is AJAX?</th>
        </tr>
    </thead>
    <tbody>
    {files}
        <tr data-active="{active}">
        	<td style="width: 70px">
            	<button class="ci-history-load" data-time="{time}">Load</button>
            </td>
            <td style="width: 140px">{datetime}</td>
            <td>{status}</td>
            <td>{method}</td>
            <td>{url}</td>
            <td>{contentType}</td>
            <td>{isAJAX}</td>
        </tr>
    {/files}
    </tbody>
</table>
