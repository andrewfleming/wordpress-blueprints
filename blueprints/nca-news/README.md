# NCA News

Demo newsroom site built with the News Corp Australia AI team: the NCA News block theme, demo articles, authors and media, plus WordPress's AI building blocks.

- Installs Gutenberg, the [AI plugin](https://wordpress.org/plugins/ai/), the Anthropic, Google and OpenAI connectors, [Co-Authors Plus](https://wordpress.org/plugins/co-authors-plus/) and [MCP Adapter](https://github.com/WordPress/mcp-adapter) (latest release).
- Installs and activates the NCA News theme from [`shared/themes/nca-news`](../../shared/themes/nca-news/).
- Downloads the demo media (about 300 MB) from the [`nca-news-v1` release](https://github.com/andrewfleming/wordpress-blueprints/releases/tag/nca-news-v1) and registers it with its original IDs and image sizes, so nothing is regenerated.
- Creates the newsroom users, then imports articles, guest authors and Guidelines from [`shared/content/nca-news.xml`](../../shared/content/nca-news.xml).
- Restores the original category IDs (the home template queries sections by ID), bylines and their order, sticky posts and permalinks. See [`setup/`](setup/) and [`shared/content/nca-news-manifest.json`](../../shared/content/nca-news-manifest.json).
- Enables the Guidelines and Workflow Palette experiments and all AI plugin features.

**You'll need your own API keys** for the AI providers. Add them on the Connectors screen, which opens when the site is ready.

Not included: the custom NCA plugins from the original demo (local avatars, polls, AI demo, Co-Authors abilities), so staff author avatars fall back to the default and poll blocks were removed from the content.

## Rebuilding the media release

The release asset is a stored (uncompressed) zip of `wp-content/uploads`, with `2026/` at the zip root:

```bash
cd wp-content/uploads && zip -r -0 nca-news-uploads.zip 2026 -x '*.DS_Store'
gh release upload nca-news-v1 nca-news-uploads.zip --clobber
```
