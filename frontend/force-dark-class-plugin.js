// Vite plugin to replace all @media (prefers-color-scheme: dark) with .dark selector
export default function forceDarkClassPlugin() {
    return {
        name: 'force-dark-class',
        enforce: 'post',
        generateBundle(options, bundle) {
            for (const [fileName, chunk] of Object.entries(bundle)) {
                if (fileName.endsWith('.css') && chunk.type === 'asset') {
                    let css = chunk.source.toString()
                    // Replace @media (prefers-color-scheme: dark) { ... } with .dark { ... }
                    css = css.replace(
                        /@media\s*\(\s*prefers-color-scheme\s*:\s*dark\s*\)\s*\{/g,
                        ':root.dark {'
                    )
                    chunk.source = css
                }
            }
        }
    }
}
