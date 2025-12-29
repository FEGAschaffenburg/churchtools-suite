<?php
/**
 * Admin Documentation Tab
 *
 * Renders markdown files from `docs/` as simple HTML (plain rendering).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$docs_dir = CHURCHTOOLS_SUITE_PATH . 'docs/';
$docs = array_values( array_filter( (array) glob( $docs_dir . '*.md' ) ) );
?>

<div class="cts-docs">
    <h2><?php esc_html_e( 'Dokumentation', 'churchtools-suite' ); ?></h2>
    <p><?php esc_html_e( 'Admin- und Entwicklerdokumentation. Wähle ein Dokument aus der Liste.', 'churchtools-suite' ); ?></p>

    <?php if ( empty( $docs ) ) : ?>
        <p><?php esc_html_e( 'Keine Dokumente gefunden.', 'churchtools-suite' ); ?></p>
    <?php else : ?>
        <div class="cts-docs-list">
            <ul>
                <?php foreach ( $docs as $doc_path ) :
                    $doc_name = basename( $doc_path );
                    $url = add_query_arg( 'doc', $doc_name );
                ?>
                    <li><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $doc_name ); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <?php
        $selected = isset( $_GET['doc'] ) ? sanitize_file_name( wp_unslash( $_GET['doc'] ) ) : basename( $docs[0] );
        $selected_path = $docs_dir . $selected;

        if ( file_exists( $selected_path ) ) {
            $content = file_get_contents( $selected_path );

            // Minimal Markdown -> HTML converter (headings, links, lists, code blocks)
            $convert_md = function( $md ) {
                // Escape HTML
                $md = esc_html( $md );

                // Code fence ```...```
                $md = preg_replace_callback('/```(.*?)```/s', function( $m ) {
                    return '<pre><code>' . esc_html( trim( $m[1] ) ) . '</code></pre>';
                }, $md);

                // Headings
                $md = preg_replace('/^###\s*(.+)$/m', '<h3>$1</h3>', $md);
                $md = preg_replace('/^##\s*(.+)$/m', '<h2>$1</h2>', $md);
                $md = preg_replace('/^#\s*(.+)$/m', '<h1>$1</h1>', $md);

                // Links [text](url)
                $md = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/', function( $m ) {
                    $text = esc_html( $m[1] );
                    $url = esc_url( $m[2] );
                    return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . $text . '</a>';
                }, $md);

                // Unordered lists (- or *)
                $md = preg_replace_callback('/(^|\n)([\-\*]\s+.+)(?=\n|$)/s', function( $m ) {
                    $items = preg_split('/\n/', trim( $m[2] ) );
                    $out = '<ul>';
                    foreach ( $items as $it ) {
                        $it = preg_replace('/^[\-\*]\s+/', '', $it);
                        $out .= '<li>' . nl2br( trim( $it ) ) . '</li>';
                    }
                    $out .= '</ul>';
                    return '\n' . $out;
                }, $md);

                // Paragraphs
                $parts = preg_split('/\n{2,}/', trim( $md ) );
                $html = '';
                foreach ( $parts as $p ) {
                    if ( preg_match('/^<(h|ul|pre|h1|h2|h3)/', trim( $p ) ) ) {
                        $html .= $p . "\n";
                    } else {
                        $html .= '<p>' . nl2br( trim( $p ) ) . '</p>\n';
                    }
                }

                return $html;
            };

            $html = $convert_md( $content );

            echo '<div class="cts-doc-content">';
            echo wp_kses( $html, array(
                'h1' => array(), 'h2' => array(), 'h3' => array(), 'p' => array(),
                'ul' => array(), 'li' => array(), 'pre' => array(), 'code' => array(),
                'a' => array( 'href' => true, 'target' => true, 'rel' => true ),
                'br' => array(),
            ) );
            echo '</div>';
        }
        ?>
    <?php endif; ?>
</div>
