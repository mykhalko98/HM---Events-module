<div class="items">
    @foreach($event_users as $event_user)
        <div class="item w-100 d-inline-block mb-3" data-id="{{ $event_user->follow_id }}">
            <div class="item-user w-50">
                {!! $event_user->getAvatar(['class' => 'image img-thumbnail-sm'], true) !!}
                <div class="info pl-3">
                    <a href="{{ $event_user->getUrl() }}" target="_blank" class="name">{{ $event_user->getName() }}</a>
                    <div class="date">
                        <span class="text-muted">{{core()->time()->format(core()->time()->localize($event_user->created_at), 'short') }}</span>
                    </div>
                </div>
            </div>
            <div class="item-options text-right float-right">
                <div class="dropdown dropdown-options ml-auto">
                    <button class="btn btn-link p-0" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <icon-image data-icon="more_horiz"></icon-image>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="wc-settings">
                        <button type="button"
                                class="btn btn-link dropdown-item text-dark py-2"
                                data-url="{{ route('events.event.unfollow_confirmation', [$event->getKey(), $event_user->getKey()]) }}"
                                data-toggle="modal"
                                data-target="#modal-event-unfollow-confirmation">
                            <icon-image data-icon="undo"></icon-image>
                            {{ __('events::lang.Delete') }}
                        </button>

                        @if($event_user->getKey() != $viewer->getKey() && core()->isNodeEnabled() && Module::has('Inbox') && Module::find('Inbox')->isEnabled() && $event_user->allowedMessaging())
                            <button class="btn btn-link dropdown-item text-dark py-2"
                                    onclick="javascript:whchat.openConversation(event, null, 'widget', this, {{$event_user->getKey()}});">
                                <icon-image data-icon="chat_bubble_outline"></icon-image><span>{{ __('users::lang.MESSAGES') }}</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>