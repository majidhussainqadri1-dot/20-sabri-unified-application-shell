# File20 V12.3 Integrated Source Privacy Review 2

P1 cache lifecycle gap: the source PWA policy changed from broad plugin-folder caching to static-only CSS/JS, but retained its old Cache API name. Cache named `sabri-shell-v140-static` would survive activation rather than being purged, retaining legacy entries. This is a source-level defect, not evidence of a Live leak.

**Fixed in this integration draft:** rotate to `sabri-shell-v123-static-public`, assert old name absent, and run exact emitted service worker activation mock asserting only legacy File20-prefixed caches deleted. No installed SW/device or Live parity evidence; staging and Live remain NOT RUN.
