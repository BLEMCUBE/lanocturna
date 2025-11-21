import { defineStore } from 'pinia'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

export const useEchoStore = defineStore('echo', {
	state: () => ({
		echo: null,
		cluster: null,
		key: null,
		channels: new Map()
	}),

	actions: {
		initEcho() {
			if (this.echo) return this.echo

			window.Pusher = Pusher

			this.echo = new Echo({
				broadcaster: 'pusher',
				key: this.key,
				cluster: this.cluster,
				forceTLS: false,
			})

			console.log('Echo inicializado')
			return this.echo



		},

		subscribeChannel(channelName) {
			if (this.channels.has(channelName)) {
				console.log(`Ya está suscrito: ${channelName}`)
				return this.channels.get(channelName)
			}

			const channel = this.echo.channel(channelName)
			this.channels.set(channelName, channel)
			console.log(`Suscrito a: ${channelName}`)

			return channel
		},
		 unsubscribe(channelName) {
      if (!this.channels.has(channelName)) return

      this.echo.leave(channelName)
      this.channels.delete(channelName)

      console.log(`👋 Canal liberado: ${channelName}`)
    },
		setKey(key) {
			this.key = key;
		},
		setCluster(cluster) {
			this.cluster = cluster;
		}
	}
})
