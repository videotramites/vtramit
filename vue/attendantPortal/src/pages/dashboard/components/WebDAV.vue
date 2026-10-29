<template>
  <div class="webdav-browser">
    <!-- Controls (upload hidden unless allowed; wired but not implemented yet) -->
    <div class="webdav-toolbar">
      <label v-if="allowUpload" class="upload">
        <input type="file" multiple @change="onUpload" />
      </label>
    </div>

    <!-- Listing -->
    <table class="webdav-table" v-if="!loading && items.length">
      <thead>
        <tr>
          <th>Name</th>
          <th class="num">Size</th>
          <th class="actions">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="entry in items" :key="entry.href">
          <td>{{ entry.name }}</td>
          <td class="num">{{ entry.isDir ? '—' : formatSize(entry.size) }}</td>
          <td class="actions">
            <a v-if="allowDownload && !entry.isDir"
               :href="entry.href"
               download
               rel="noopener"
               target="_blank">Download</a>

            <button v-if="allowDelete && !entry.isDir"
                    type="button"
                    @click="onDelete(entry)"
                    :disabled="busy">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>

    <p v-if="!loading && !items.length">No files found.</p>
    <p v-if="loading">Loading…</p>
    <p v-if="error" class="error">{{ error }}</p>
  </div>
</template>

<script>
/**
 * WebDavBrowser (Vue 2)
 *
 * - Lists files from a Nextcloud WebDAV collection using PROPFIND (Depth: 1).
 * - Respects allowDelete / allowUpload / allowDownload props (download implemented in this step).
 * - Uses session cookies (same-origin). No tokens required.
 */
export default {
  name: 'WebDavBrowser',
  props: {
    webdavUrl: { type: String, required: true }, // Collection URL (e.g. /remote.php/dav/files/USER/path/)
    allowDelete: { type: Boolean, default: false },
    allowUpload: { type: Boolean, default: false },
    allowDownload: { type: Boolean, default: true },
    refreshOnMount: { type: Boolean, default: true },
  },
  data () {
    return {
      items: [],
      loading: false,
      busy: false,
      error: null,
    }
  },
  created () {
    if (this.refreshOnMount) this.fetchListing()
  },
  methods: {
    /**
     * Fetch listing via PROPFIND Depth:1 and parse multi-status XML.
     */
    async fetchListing () {
      this.loading = true
      this.error = null
      try {
        const body =
          `<?xml version="1.0" encoding="utf-8"?>` +
          `<d:propfind xmlns:d="DAV:">` +
          `<d:prop>` +
          `<d:resourcetype/>` +
          `<d:getcontentlength/>` +
          `<d:getcontenttype/>` +
          `</d:prop>` +
          `</d:propfind>`

        const res = await fetch(this.webdavUrl, {
          method: 'PROPFIND',
          headers: {
            'Depth': '1',
            'Content-Type': 'application/xml; charset=utf-8',
          },
          credentials: 'same-origin',
          body,
        })

        if (!res.ok) throw new Error(`PROPFIND failed: ${res.status} ${res.statusText}`)
        const xml = await res.text()
        this.items = this.parsePropfind(xml, this.webdavUrl)
      } catch (e) {
        this.error = e.message || String(e)
      } finally {
        this.loading = false
      }
    },

    /**
     * Parse WebDAV multistatus XML into a list of items.
     * Skips the collection root itself and keeps both files and directories.
     */
    parsePropfind (xmlString, baseUrl) {
      const parser = new DOMParser()
      const xml = parser.parseFromString(xmlString, 'application/xml')
      const responses = Array.from(xml.getElementsByTagNameNS('DAV:', 'response'))
      const base = new URL(baseUrl, window.location.origin)
      const baseHref = decodeURIComponent(base.pathname.endsWith('/') ? base.pathname : base.pathname + '/')

      const items = responses.map(r => {
        const hrefNode = r.getElementsByTagNameNS('DAV:', 'href')[0]
        if (!hrefNode) return null
        const rawHref = hrefNode.textContent
        const absolute = new URL(rawHref, base)
        const href = absolute.toString()

        // Properties
        const prop = r.getElementsByTagNameNS('DAV:', 'prop')[0]
        const resourcetype = prop ? prop.getElementsByTagNameNS('DAV:', 'resourcetype')[0] : null
        const isDir = !!(resourcetype && resourcetype.getElementsByTagNameNS('DAV:', 'collection').length)
        const sizeNode = prop ? prop.getElementsByTagNameNS('DAV:', 'getcontentlength')[0] : null
        const size = sizeNode ? Number(sizeNode.textContent || 0) : 0

        // Name from path (decode for display)
        const path = decodeURIComponent(absolute.pathname)
        const parts = path.split('/').filter(Boolean)
        const name = parts.length ? parts[parts.length - 1] : '/'

        return { href, isDir, size, name, path }
      }).filter(Boolean)
        // Remove the collection root entry (same path as base)
        .filter(entry => decodeURIComponent(new URL(entry.href).pathname) !== baseHref)

      // Directories first, then files, alphabetical
      return items.sort((a, b) => {
        if (a.isDir !== b.isDir) return a.isDir ? -1 : 1
        return a.name.localeCompare(b.name, undefined, { sensitivity: 'base' })
      })
    },

    /**
     * Format size in a human-friendly way.
     */
    formatSize (bytes) {
      if (!bytes) return '0 B'
      const units = ['B', 'KB', 'MB', 'GB', 'TB']
      let i = 0
      let n = bytes
      while (n >= 1024 && i < units.length - 1) {
        n /= 1024
        i++
      }
      return `${n.toFixed(n < 10 && i > 0 ? 1 : 0)} ${units[i]}`
    },

    /**
     * Placeholder: upload handler (wired, implemented next step).
     */
    async onUpload (evt) {
      // Will be implemented in next step if you confirm structure.
      evt.target.value = ''
    },

    /**
     * Placeholder: delete handler (wired, implemented next step).
     */
    async onDelete (entry) {
      // Will be implemented in next step if you confirm structure.
    },
  },
}
</script>

<style scoped>
.webdav-browser { font-size: 14px; }
.webdav-toolbar { margin-bottom: 8px; }
.webdav-table { width: 100%; border-collapse: collapse; }
.webdav-table th, .webdav-table td { padding: 6px 8px; border-bottom: 1px solid #ddd; }
.webdav-table th.num, .webdav-table td.num { text-align: right; width: 120px; }
.webdav-table th.actions, .webdav-table td.actions { white-space: nowrap; text-align: right; }
.error { color: #b00020; }
</style>
