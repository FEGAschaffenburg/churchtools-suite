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

<div class="wrap cts-wrap">

    <div class="cts-header">
        <h1>
            <span>📚</span>
            <?php esc_html_e( 'Dokumentation', 'churchtools-suite' ); ?>
        </h1>
        <p class="cts-subtitle"><?php esc_html_e( 'Alle Dokumente und Anleitungen im Überblick', 'churchtools-suite' ); ?></p>
    </div>

    <div class="cts-docs-container">
        <aside class="cts-docs-sidebar cts-card">
            <div class="cts-card-header"><h3><?php esc_html_e( 'Menü', 'churchtools-suite' ); ?></h3></div>
            <div class="cts-card-body">
                <ul class="cts-docs-nav">
                    <?php foreach ( $docs as $doc_path ) :
                        $doc_file = basename( $doc_path );
                        // Create a readable title from filename
                        $title = preg_replace( '/\.md$/', '', $doc_file );
                        $title = str_replace( array( '-', '_' ), ' ', $title );
                        $title = ucwords( $title );
                        $url = add_query_arg( 'doc', $doc_file );
                        $active = ( isset( $_GET['doc'] ) && sanitize_file_name( wp_unslash( $_GET['doc'] ) ) === $doc_file ) || ( ! isset( $_GET['doc'] ) && $doc_path === $docs[0] );
                    ?>
                        <li class="<?php echo $active ? 'active' : ''; ?>"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>

        <main class="cts-docs-main">
            <div class="cts-card">
                <div class="cts-card-header"><h3><?php esc_html_e( 'Inhalt', 'churchtools-suite' ); ?></h3></div>
                <div class="cts-card-body">
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

                    echo wp_kses( $html, array(
                        'h1' => array(), 'h2' => array(), 'h3' => array(), 'p' => array(),
                        'ul' => array(), 'li' => array(), 'pre' => array(), 'code' => array(),
                        'a' => array( 'href' => true, 'target' => true, 'rel' => true ),
                        'br' => array(),
                    ) );
                } else {
                    echo '<p>' . esc_html__( 'Ausgewähltes Dokument nicht gefunden.', 'churchtools-suite' ) . '</p>';
                }
                ?>
                </div>
            </div>
        </main>
    </div>

</div>
