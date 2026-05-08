@php
    $roomUrl = $appointment->videoRoom?->room_url;
    $roomName = $appointment->videoRoom?->external_room_id ?: trim((string) parse_url((string) $roomUrl, PHP_URL_PATH), '/');
    $participantName = $participantName ?? 'Guest';
    $meetingContainerId = $meetingContainerId ?? 'jitsi-container';
    $participantListId = $participantListId ?? 'jitsi-participants';
    $meetingDomain = parse_url((string) $roomUrl, PHP_URL_HOST) ?: 'meet.jit.si';
@endphp

@if($roomName)
    <div class="video-frame" style="margin-top:16px;">
        <div id="{{ $meetingContainerId }}" style="width:100%;min-height:540px;"></div>
    </div>
    <script src="https://meet.jit.si/external_api.js"></script>
    <script>
        (function () {
            const container = document.getElementById(@json($meetingContainerId));
            const participantList = document.getElementById(@json($participantListId));
            if (!container || typeof JitsiMeetExternalAPI === 'undefined') {
                return;
            }

            const participants = new Map();
            const renderParticipants = () => {
                if (!participantList) return;
                if (!participants.size) {
                    participantList.innerHTML = '<div class="meta-item">No live participants detected yet.</div>';
                    return;
                }

                participantList.innerHTML = Array.from(participants.values()).map((name) => (
                    '<div class="meta-item"><strong>' + name + '</strong><div class="muted" style="margin-top:6px;">Connected in the meeting</div></div>'
                )).join('');
            };

            const api = new JitsiMeetExternalAPI(@json($meetingDomain), {
                roomName: @json($roomName),
                parentNode: container,
                userInfo: {
                    displayName: @json($participantName),
                },
                configOverwrite: {
                    prejoinPageEnabled: false,
                    startWithAudioMuted: false,
                    startWithVideoMuted: false,
                },
                interfaceConfigOverwrite: {
                    MOBILE_APP_PROMO: false,
                    SHOW_JITSI_WATERMARK: false,
                    SHOW_WATERMARK_FOR_GUESTS: false,
                    DEFAULT_BACKGROUND: '#eef5f7',
                }
            });

            api.addListener('videoConferenceJoined', (event) => {
                participants.set(event.id || 'self', @json($participantName));
                renderParticipants();
            });

            api.addListener('participantJoined', (event) => {
                participants.set(event.id, event.displayName || 'Participant');
                renderParticipants();
            });

            api.addListener('participantLeft', (event) => {
                participants.delete(event.id);
                renderParticipants();
            });

            renderParticipants();
        })();
    </script>
@endif
