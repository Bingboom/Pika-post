<?php

namespace GRIM_SG\Vendor;

use GRIM_SG\GoogleNews;
use GRIM_SG\ImageSitemap;
use GRIM_SG\VideoSitemap;

class SitemapGenerator {
	public $sitemapFileName = 'sitemap.xml';

	public $sitemapIndexFileName = 'sitemap-index.xml';

	public $robotsFileName = 'robots.txt';

	public $maxURLsPerSitemap = 50000;

	public $createGZipFile = false;

	private $baseURL;

	private $basePath;

	private $searchEngines = array(
		array(
			'https://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid=USERID&url=',
			'https://search.yahooapis.com/SiteExplorerService/V1/ping?sitemap=',
		),
		'https://www.google.com/webmasters/tools/ping?sitemap=',
		'https://submissions.ask.com/ping?sitemap=',
		'https://www.bing.com/webmaster/ping.aspx?siteMap=',
	);

	private $urls = array();

	private $sitemaps;

	private $sitemapIndex;

	private $sitemapFullURL;

	/**
	 * Constructor.
	 *
	 * @param string $baseURL You site URL, with / at the end.
	 * @param string|null $basePath Relative path where sitemap and robots should be stored.
	 */
	public function __construct( $baseURL, $basePath = "" ) {
		$this->baseURL  = $baseURL;
		$this->basePath = $basePath;
	}

	public function addUrls( $urls_array, $callback = 'addUrl', $template = 'sitemap' ) {
		if ( ! is_array( $urls_array ) ) {
			throw new \InvalidArgumentException( 'Array as argument should be given.' );
		}

		if ( sgg_is_sitemap_index( $template ) ) {
			foreach ( $urls_array as $sitemap => $inner_urls ) {
				foreach ( $inner_urls as $url ) {
					$this->{$callback}( $url, $sitemap );
				}
			}
		} else {
			foreach ( $urls_array as $url ) {
				$this->{$callback}( $url );
			}
		}
	}

	/**
	 * Use this to add single URL to Google News Sitemap.
	 */
	public function addNewsUrl( $item ) {
		$url = $item[0] ?? null;

		$this->validateUrl( $url );

		$tmp                     = array();
		$tmp['loc']              = $url;
		$tmp['publication_name'] = $item[1] ?? null;
		$tmp['language']         = $item[2] ?? null;
		$tmp['title']            = $item[3] ?? null;

		if ( isset( $item[4] ) ) {
			$tmp['lastmod'] = $item[4];
		}

		if ( sgg_pro_enabled() ) {
			$tmp['keywords']      = implode( ', ', apply_filters( 'sgg_news_keywords', array(), $item[5] ?? null ) );
			$tmp['stock_tickers'] = implode( ', ', apply_filters( 'sgg_news_stock_tickers', array(), $item[5] ?? null ) );
		}

		$this->urls[] = $tmp;
	}

	/**
	 * Use this to add single URL to Sitemap.
	 */
	public function addMediaUrl( $item ) {
		$url = $item[0] ?? null;

		$this->validateUrl( $url );

		$tmp        = array();
		$tmp['loc'] = $url;

		if ( isset( $item[1] ) ) {
			$tmp['media'] = $item[1];
		}

		$this->urls[] = $tmp;
	}

	/**
	 * Use this to add single URL to Sitemap.
	 */
	public function addUrl( $item, $sitemap = null ) {
		$url = $item[0] ?? null;

		$this->validateUrl( $url );

		$tmp        = array();
		$tmp['loc'] = $url;

		if ( isset( $item[1] ) ) {
			$tmp['lastmod'] = $item[1];
		}
		if ( isset( $item[2] ) ) {
			$tmp['changefreq'] = $item[2];
		}
		if ( isset( $item[3] ) ) {
			$tmp['priority'] = $item[3];
		}

		if ( ! empty( $sitemap ) ) {
			$this->urls[ $sitemap ][] = $tmp;
		} else {
			$this->urls[] = $tmp;
		}
	}

	/**
	 * Validate Sitemap Item URL
	 */
	public function validateUrl( $url ) {
		if ( null === $url ) {
			throw new \InvalidArgumentException( 'URL is mandatory. At least one argument should be given.' );
		}

		$urlLenght = extension_loaded( 'mbstring' ) ? mb_strlen( $url ) : strlen( $url );
		if ( $urlLenght > 2048 ) {
			throw new \InvalidArgumentException(
				"URL lenght can't be bigger than 2048 characters.
                        Note, that precise url length check is guaranteed only using mb_string extension.
                        Make sure Your server allow to use mbstring extension."
			);
		}
	}

	/**
	 * Create sitemap in memory.
	 */
	public function createSitemap( $template = 'sitemap', $headers = array(), $is_xml = true ) {
		if ( ! isset( $this->urls ) ) {
			throw new \BadMethodCallException( 'To create sitemap, call addUrl or addUrls function first.' );
		}

		if ( $this->maxURLsPerSitemap > 50000 ) {
			throw new \InvalidArgumentException( 'More than 50,000 URLs per single sitemap is not allowed.' );
		}

		$settings        = ( new Controller() )->get_settings();
		$stylesheet_path = apply_filters( 'sitemap_xsl_template_path', 'sitemap-stylesheet.xsl' );
		$stylesheet_url  = sgg_get_sitemap_url( "{$stylesheet_path}?template={$template}", "sitemap_xsl={$template}", false );

		$sitemap_urls = sgg_is_sitemap_index( $template, $settings )
			? $this->urls
			: array_chunk( $this->urls, $this->maxURLsPerSitemap );

		foreach ( $sitemap_urls as $sitemap_key => $sitemap ) {
			$max_datetime = null;
			$dom          = $this->create_sitemap_dom( $stylesheet_url );
			$urlset       = $this->create_sitemap_urlset( $dom, $headers );

			$dom->appendChild( $urlset );

			foreach ( $sitemap as $url ) {
				$url_element = $dom->createElement( 'url' );

				$url_element->appendChild(
					$dom->createElement( 'loc', htmlspecialchars( $url['loc'], ENT_QUOTES, 'UTF-8' ) )
				);

				if ( GoogleNews::$template === $template ) {
					if ( GoogleNews::is_older_than_48h( $url['lastmod'] ) ) {
						$url_element->appendChild(
							$dom->createElement( 'lastmod', $url['lastmod'] )
						);
					} else {
						$news = $url_element->appendChild( $dom->createElement( 'news:news' ) );

						if ( isset( $url['publication_name'] ) ) {
							$publication = $dom->createElement( 'news:publication' );
							$news->appendChild( $publication );

							$publication->appendChild(
								$dom->createElement( 'news:name', esc_html( $url['publication_name'] ) )
							);
							$publication->appendChild(
								$dom->createElement( 'news:language', $url['language'] )
							);
						}
						if ( isset( $url['lastmod'] ) ) {
							$news->appendChild(
								$dom->createElement( 'news:publication_date', $url['lastmod'] )
							);
						}
						if ( isset( $url['title'] ) ) {
							$news->appendChild(
								$dom->createElement( 'news:title', esc_html( $url['title'] ) )
							);
						}
						if ( sgg_pro_enabled() ) {
							$news->appendChild(
								$dom->createElement( 'news:keywords', $url['keywords'] )
							);
							$news->appendChild(
								$dom->createElement( 'news:stock_tickers', $url['stock_tickers'] )
							);
						}
					}
				} elseif ( ImageSitemap::$template === $template ) {
					if ( ! empty( $url['media'] ) ) {
						foreach ( $url['media'] as $image ) {
							$image_element = $url_element->appendChild( $dom->createElement( 'image:image' ) );
							$image_element->appendChild(
								$dom->createElement( 'image:loc', $image )
							);
						}
					}
				} elseif ( VideoSitemap::$template === $template ) {
					if ( ! empty( $url['media'] ) ) {
						foreach ( $url['media'] as $video ) {
							$video_element = $url_element->appendChild( $dom->createElement( 'video:video' ) );
							$video_element->appendChild(
								$dom->createElement( 'video:thumbnail_loc', esc_url( $video['thumbnail'] ?? '' ) )
							);
							$video_element->appendChild(
								$dom->createElement( 'video:title', esc_html( $video['title'] ?? '' ) )
							);
							$video_element->appendChild(
								$dom->createElement( 'video:description', esc_html( $video['description'] ?? '' ) )
							);
							$video_element->appendChild(
								$dom->createElement( 'video:player_loc', esc_url( $video['player_loc'] ?? '' ) )
							);
							$video_element->appendChild(
								$dom->createElement( 'video:duration', esc_html( $video['duration'] ?? '' ) )
							);
						}
					}
				} else {
					if ( isset( $url['lastmod'] ) ) {
						$url_element->appendChild(
							$dom->createElement( 'lastmod', $url['lastmod'] )
						);

						if ( null === $max_datetime || strtotime( $url['lastmod'] ) > strtotime( $max_datetime ) ) {
							$max_datetime = $url['lastmod'];
						}
					}
					if ( isset( $url['changefreq'] ) ) {
						$url_element->appendChild(
							$dom->createElement( 'changefreq', $url['changefreq'] )
						);
					}
					if ( isset( $url['priority'] ) ) {
						$url_element->appendChild(
							$dom->createElement( 'priority', $url['priority'] )
						);
					}
				}

				$urlset->appendChild( $url_element );
			}

			if ( ! sgg_pro_enabled() || ! $settings->minimize_sitemap ) {
				$dom->formatOutput = true;
			}

			$ready_sitemap = $dom->saveXML();

			if ( sgg_is_sitemap_index( $template, $settings ) ) {
				$this->sitemaps[ $sitemap_key ][]          = $ready_sitemap;
				$this->sitemaps[ $sitemap_key ]['lastmod'] = $max_datetime ?? gmdate( 'c' );
			} else {
				$this->sitemaps[] = $ready_sitemap;
			}
		}

		if ( null === $this->sitemaps ) {
			$dom    = $this->create_sitemap_dom( $stylesheet_url );
			$urlset = $this->create_sitemap_urlset( $dom, $headers );

			$dom->appendChild( $urlset );

			$this->sitemaps[] = $dom->saveXML();
		}

		if ( count( $this->sitemaps ) > 1000 ) {
			throw new \LengthException( 'Sitemap index can contains 1000 single sitemaps. Perhaps You trying to submit too many URLs.' );
		}

		if ( count( $this->sitemaps ) > 1 ) {
			$dom = $this->create_sitemap_dom(
				sgg_get_sitemap_url( "{$stylesheet_path}?template=sitemap-index", 'sitemap_xsl=sitemap-index', false )
			);

			$sitemapindex = $dom->createElement( 'sitemapindex' );
			$sitemapindex->setAttribute( 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance' );
			$sitemapindex->setAttribute( 'xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd' );
			$sitemapindex->setAttribute( 'xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9' );
			$dom->appendChild( $sitemapindex );

			foreach ( $this->sitemaps as $sitemap_key => $sitemap ) {
				$sitemap_element = $dom->createElement( 'sitemap' );
				$sitemap_type    = $is_xml ? 'xml' : 'html';

				$sitemap_element->appendChild(
					$dom->createElement(
						'loc',
						esc_url( sgg_get_sitemap_url( "{$sitemap_key}-sitemap.{$sitemap_type}", "sitemap_{$sitemap_type}=true&sitemap={$sitemap_key}", false ) )
					)
				);

				$sitemap_element->appendChild(
					$dom->createElement( 'lastmod', $sitemap['lastmod'] ?? gmdate( 'c' ) )
				);

				$sitemapindex->appendChild( $sitemap_element );
			}

			if ( $is_xml && $settings->enable_image_sitemap ) {
				$sitemap_element = $dom->createElement( 'sitemap' );

				$sitemap_element->appendChild(
					$dom->createElement( 'loc', sgg_get_sitemap_url( $settings->image_sitemap_url, 'image_sitemap' ) )
				);

				$sitemap_element->appendChild(
					$dom->createElement( 'lastmod', gmdate( 'c' ) )
				);

				$sitemapindex->appendChild( $sitemap_element );
			}

			if ( $is_xml && $settings->enable_video_sitemap ) {
				$sitemap_element = $dom->createElement( 'sitemap' );

				$sitemap_element->appendChild(
					$dom->createElement( 'loc', sgg_get_sitemap_url( $settings->video_sitemap_url, 'video_sitemap' ) )
				);

				$sitemap_element->appendChild(
					$dom->createElement( 'lastmod', gmdate( 'c' ) )
				);

				$sitemapindex->appendChild( $sitemap_element );
			}

			if ( ! sgg_pro_enabled() || ! $settings->minimize_sitemap ) {
				$dom->formatOutput = true;
			}

			$this->sitemapFullURL = $this->baseURL . $this->sitemapIndexFileName;
			$this->sitemapIndex   = array(
				$this->sitemapIndexFileName,
				$dom->saveXML(),
			);
		} else {
			if ( $this->createGZipFile ) {
				$this->sitemapFullURL = $this->baseURL . $this->sitemapFileName . '.gz';
			} else {
				$this->sitemapFullURL = $this->baseURL . $this->sitemapFileName;
			}
			$this->sitemaps[0] = array(
				$this->sitemapFileName,
				$this->sitemaps[0],
			);
		}
	}

	public function create_sitemap_dom( $stylesheet_url ) {
		$generator_info = 'sitemap-generator-url="https://wpgrim.com" sitemap-generator-version="' . GRIM_SG_VERSION . '"';

		$dom = new \DOMDocument( '1.0', 'UTF-8' );

		$dom->appendChild(
			$dom->createProcessingInstruction(
				'xml-stylesheet',
				'type="text/xsl" href="' . $stylesheet_url . '"'
			)
		);

		$dom->appendChild( $dom->createComment( $generator_info ) );

		return $dom;
	}

	public function create_sitemap_urlset( $dom, $headers = array() ) {
		$urlset = $dom->createElement( 'urlset' );

		$urlset->setAttribute( 'xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9' );
		if ( ! empty( $headers ) ) {
			foreach ( $headers as $key => $header ) {
				$urlset->setAttribute( $key, $header );
			}
		}

		return $urlset;
	}

	/**
	 * Returns created sitemaps as array of strings.
	 * Use it You want to work with sitemap without saving it as files.
	 * @return array of strings
	 * @access public
	 */
	public function toArray() {
		if ( isset( $this->sitemapIndex ) ) {
			return array_merge( array( $this->sitemapIndex ), $this->sitemaps );
		} else {
			return $this->sitemaps;
		}
	}

	/**
	 * Will print sitemaps.
	 * @access public
	 */
	public function outputSitemap( $template, $is_xml, $inner_sitemap = null ) {
		add_filter( 'trp_stop_translating_page', '__return_true' );

		ob_get_clean();

		if ( $is_xml ) {
			header( 'Content-Type: text/xml; charset=utf-8' );
		} else {
			ob_start();
		}

		if ( ! empty( $inner_sitemap ) && sgg_is_sitemap_index( $template ) && ! empty( $this->sitemaps[ $inner_sitemap ][0] ) ) {
			echo $this->sitemaps[ $inner_sitemap ][0];
		} else {
			if ( ! empty( $this->sitemapIndex[1] ) ) {
				$template = 'sitemap-index';
				echo $this->sitemapIndex[1];
			} else {
				echo $this->sitemaps[0][1] ?? '';
			}
		}

		if ( ! $is_xml ) {
			$xml_source = ob_get_clean();

			$xml = new \DOMDocument();
			$xml->loadXML( $xml_source );

			ob_start();

			load_template(
				GRIM_SG_PATH . '/templates/xsl/sitemap.php',
				false,
				compact( 'template', 'is_xml' )
			);

			$xsl_content = ob_get_clean();

			$xsl = new \DOMDocument();
			$xsl->loadXML( $xsl_content );

			$proc = new \XSLTProcessor();
			$proc->importStyleSheet( $xsl );

			$dom_tran_obj = $proc->transformToDoc( $xml );

			foreach ( $dom_tran_obj->childNodes as $node ) {
				echo $dom_tran_obj->saveXML( $node ) . "\n";
			}
		}

		if ( ob_get_contents() ) {
			ob_end_flush();
		}
	}

	/**
	 * Will write sitemaps as files.
	 * @access public
	 */
	public function writeSitemap() {
		if ( ! isset( $this->sitemaps ) ) {
			throw new \BadMethodCallException( 'To write sitemap, call createSitemap function first.' );
		}
		if ( isset( $this->sitemapIndex ) ) {
			$this->_writeFile( $this->sitemapIndex[1], $this->basePath, $this->sitemapIndex[0] );
			foreach ( $this->sitemaps as $sitemap ) {
				$this->_writeGZipFile( $sitemap[1], $this->basePath, $sitemap[0] );
			}
		} else {
			$this->_writeFile( $this->sitemaps[0][1], $this->basePath, $this->sitemaps[0][0] );
			if ( $this->createGZipFile ) {
				$this->_writeGZipFile( $this->sitemaps[0][1], $this->basePath, $this->sitemaps[0][0] . ".gz" );
			}
		}
	}

	/**
	 * If robots.txt file exist, will update information about newly created sitemaps.
	 * If there is no robots.txt will, create one and put into it information about sitemaps.
	 * @access public
	 */
	public function updateRobots() {
		if ( ! isset( $this->sitemaps ) ) {
			throw new \BadMethodCallException( 'To update robots.txt, call createSitemap function first.' );
		}
		$sampleRobotsFile = "User-agent: *\nAllow: /";
		if ( file_exists( $this->basePath . $this->robotsFileName ) ) {
			$robotsFile        = explode( "\n", file_get_contents( $this->basePath . $this->robotsFileName ) );
			$robotsFileContent = "";
			foreach ( $robotsFile as $key => $value ) {
				if ( substr( $value, 0, 8 ) == 'Sitemap:' ) {
					unset( $robotsFile[ $key ] );
				} else {
					$robotsFileContent .= $value . "\n";
				}
			}
			$robotsFileContent .= "Sitemap: $this->sitemapFullURL";
			if ( $this->createGZipFile && ! isset( $this->sitemapIndex ) ) {
				$robotsFileContent .= "\nSitemap: " . $this->sitemapFullURL . ".gz";
			}
			file_put_contents( $this->basePath . $this->robotsFileName, $robotsFileContent );
		} else {
			$sampleRobotsFile = $sampleRobotsFile . "\n\nSitemap: " . $this->sitemapFullURL;
			if ( $this->createGZipFile && ! isset( $this->sitemapIndex ) ) {
				$sampleRobotsFile .= "\nSitemap: " . $this->sitemapFullURL . ".gz";
			}
			file_put_contents( $this->basePath . $this->robotsFileName, $sampleRobotsFile );
		}
	}

	/**
	 * Will inform search engines about newly created sitemaps.
	 * Google, Ask, Bing and Yahoo will be noticed.
	 * If You don't pass yahooAppId, Yahoo still will be informed,
	 * but this method can be used once per day. If You will do this often,
	 * message that limit was exceeded  will be returned from Yahoo.
	 *
	 * @param string $yahooAppId Your site Yahoo appid.
	 *
	 * @return array of messages and http codes from each search engine
	 * @access public
	 */
	public function submitSitemap( $yahooAppId = null ) {
		if ( ! isset( $this->sitemaps ) ) {
			throw new \BadMethodCallException( 'To submit sitemap, call createSitemap function first.' );
		}

		if ( ! extension_loaded( 'curl' ) ) {
			throw new \BadMethodCallException( 'cURL library is needed to do submission.' );
		}

		$searchEngines    = $this->searchEngines;
		$searchEngines[0] = isset( $yahooAppId ) ? str_replace( 'USERID', $yahooAppId, $searchEngines[0][0] ) : $searchEngines[0][1];
		$result           = array();

		for ( $i = 0; $i < sizeof( $searchEngines ); $i ++ ) {
			$submitSite = curl_init( $searchEngines[ $i ] . htmlspecialchars( $this->sitemapFullURL, ENT_QUOTES, 'UTF-8' ) );
			curl_setopt( $submitSite, CURLOPT_RETURNTRANSFER, true );
			$responseContent = curl_exec( $submitSite );
			$response        = curl_getinfo( $submitSite );
			$submitSiteShort = array_reverse( explode( '.', parse_url( $searchEngines[ $i ], PHP_URL_HOST ) ) );
			$result[]        = array(
				'site'      => $submitSiteShort[1] . '.' . $submitSiteShort[0],
				'fullsite'  => $searchEngines[ $i ] . htmlspecialchars( $this->sitemapFullURL, ENT_QUOTES, 'UTF-8' ),
				'http_code' => $response['http_code'],
				'message'   => str_replace( "\n", ' ', strip_tags( $responseContent ) )
			);
		}

		return $result;
	}

	/**
	 * Save file.
	 *
	 * @param string $content
	 * @param string $filePath
	 * @param string $fileName
	 *
	 * @return bool
	 * @access private
	 */
	private function _writeFile( $content, $filePath, $fileName ) {
		$file = fopen( $filePath . $fileName, 'w' );
		fwrite( $file, $content );

		return fclose( $file );
	}

	/**
	 * Save GZipped file.
	 *
	 * @param string $content
	 * @param string $filePath
	 * @param string $fileName
	 *
	 * @return bool
	 * @access private
	 */
	private function _writeGZipFile( $content, $filePath, $fileName ) {
		$file = gzopen( $filePath . $fileName, 'w' );
		gzwrite( $file, $content );

		return gzclose( $file );
	}
}
