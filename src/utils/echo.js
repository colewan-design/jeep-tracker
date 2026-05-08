import Echo from 'laravel-echo';
import PusherModule from 'pusher-js';

const Pusher = PusherModule.Pusher ?? PusherModule;
global.Pusher = Pusher;

const echo = new Echo({
  broadcaster: 'pusher',
  key: '0f12289adb56149777a9',
  cluster: 'ap1',
  forceTLS: true,
});

export default echo;
