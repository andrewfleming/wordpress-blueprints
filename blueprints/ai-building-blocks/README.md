# AI Building Blocks

Demo environment for WordPress core's AI building blocks, along with plugins that implement features on top of these new APIs.

- Installs Gutenberg, the [AI plugin](https://wordpress.org/plugins/ai/) (experiments, Abilities Explorer, Connectors UI), and connectors for OpenAI, Anthropic, and Google.
- Installs [MCP Adapter](https://github.com/WordPress/mcp-adapter) and SEOPress (with its admin columns hidden and setup wizard skipped).
- Ships two mu-plugins: one that raises the HTTP timeout for AI provider requests (image generation can be slow), and a stopgap `file_exists()` guard for [WordPress/php-ai-client#258](https://github.com/WordPress/php-ai-client/issues/258).
- Raises the PHP memory limit for image-generation post-processing.
- Enables the Guidelines experiment and imports example Media, Enterprise, and Public Sector posts (see [`shared/content/ai-building-blocks+guidelines.xml`](../../shared/content/ai-building-blocks+guidelines.xml)).
- Enables all of the AI plugin's features.

**You'll need your own API keys** for whichever AI providers (OpenAI, Anthropic, Google) you want to exercise — add them via the Connectors UI (the landing page) after the site boots.

## Open in Studio / Playground

Use the "Open in Studio" button in the [root README](../../README.md).

Or with the CLI:

```bash
npx @wp-playground/cli server --blueprint=./blueprints/ai-building-blocks/blueprint.json
```
