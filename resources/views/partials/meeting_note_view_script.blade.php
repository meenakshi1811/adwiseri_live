<script>
function escapeMeetingNoteHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function viewMeetingNote(note) {
    const discussion = escapeMeetingNoteHtml(note.discussion || '').replace(/\n/g, '<br>');
    Swal.fire({
        title: 'Meeting Note Details',
        html: `
            <div class="text-start">
                <p><strong>User:</strong> ${escapeMeetingNoteHtml(note.user || '')}</p>
                <p><strong>Client:</strong> ${escapeMeetingNoteHtml(note.client || '')}</p>
                <p><strong>Application:</strong> ${escapeMeetingNoteHtml(note.application || '')}</p>
                <p><strong>Mode:</strong> ${escapeMeetingNoteHtml(note.mode || '')}</p>
                <p><strong>Date:</strong> ${escapeMeetingNoteHtml(note.date || '')}</p>
                <p><strong>Discussion:</strong></p>
                <div style="max-height:300px;overflow:auto;text-align:left;white-space:normal;">${discussion}</div>
            </div>
        `,
        width: '640px',
        confirmButtonText: 'Close'
    });
}

document.addEventListener('click', function (event) {
    const trigger = event.target.closest('.js-view-meeting-note');
    if (!trigger) {
        return;
    }

    try {
        viewMeetingNote(JSON.parse(trigger.getAttribute('data-meeting-note') || '{}'));
    } catch (error) {
        console.error('Unable to open meeting note details.', error);
    }
});
</script>
