# ChurchTools Suite v1.2.1.4

- Fix: Deleted ChurchTools appointments are now detected correctly during incremental sync.
- Fix: Event delete cleanup now considers nested event appointment IDs from `event.appointment.base.id` and `event.appointment.id`.
- Fix: Date normalization no longer defaults invalid or missing timestamps to the current time in sync cleanup keys.
