<template>
  <section
    id="fullscreen-map-view"
    class="mx-n4"
  >
    <div class="mapouter">
      <div class="gmap_canvas">
        <iframe
          id="gmap_canvas"
          frameborder="0"
          height="100%"
          marginheight="0"
          marginwidth="0"
          scrolling="no"
          src="https://maps.google.com/maps?q=google&t=&z=13&ie=UTF8&iwloc=&output=embed"
          width="100%"
        />
      </div>
    </div>
  </section>
</template>

<script>
  export default {
    name: 'FullscreenMapView',
  }
</script>

<style lang="sass">
  .mapouter
    text-align: right
    height: 100%
    width: 100%
    position:  absolute

  .gmap_canvas
    overflow: hidden
    background: none !important
    height: 100%
    width: 100%
</style>
