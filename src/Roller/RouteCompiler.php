<?php
namespace Roller;

class RouteCompiler
{
    /**
     * Compiles the current route instance.
     */
    static function compile(Array $route)
    {
        $pattern = $route['pattern'];
        $len = strlen($pattern);


        /**
         * contains:
         *   
         *   array( 'text', $text ),
         *   array( 'variable', $match[0][0][0], $regexp, $var);
         *
         */
        $tokens = array();
        $variables = array();
        $pos = 0;
        preg_match_all('#.\{([\w\d_]+)\}#', $pattern, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach ($matches as $match) {

            /*
             * Split tokens from abstract pattern
             * to rebuild regexp pattern.
             */
            if ($text = substr($pattern, $pos, $match[0][1] - $pos)) {
                $tokens[] = array('text', $text);
            }

            // the first char from pattern (seperater)
            $seps = array($pattern[$pos]);
            $pos = $match[0][1] + strlen($match[0][0]);

            // field name
            $var = $match[1][0];


            /* build field pattern from requirement */
            if ( isset( $route['requirement'][$var] ) && $req = $route['requirement'][$var]) {
                $regexp = $req;
            } else {
                if ($pos !== $len) {
                    $seps[] = $pattern[$pos];
                }
                $regexp = sprintf('[^%s]+?', preg_quote(implode('', array_unique($seps)), '#'));
            }

            $tokens[] = array('variable', $match[0][0][0], $regexp, $var);
            $variables[] = $var;
        }

        if ($pos < $len) {
            $tokens[] = array('text', substr($pattern, $pos));
        }

        // find the first optional token
        $firstOptional = INF;
        for ($i = count($tokens) - 1; $i >= 0; $i--) {
            if ('variable' === $tokens[$i][0] 
                && isset($route['default'][ $tokens[$i][3] ]) )
            {
                $firstOptional = $i;
            } 
            else 
            {
                break;
            }
        }

        // compute the matching regexp
        $regex = '';
        $indent = 1;

        // first optional token and only one token.
        if (1 === count($tokens) && 0 === $firstOptional) {
            $token = $tokens[0];
            ++$indent;
            $regex .= str_repeat(' ', $indent * 4).sprintf("%s(?:\n", preg_quote($token[1], '#'));

            // regular expression with place holder name. ( [3] => name , [2] => pattern
            $regex .= str_repeat(' ', $indent * 4).sprintf("(?P<%s>%s)\n", $token[3], $token[2]);
        } else {
            foreach ($tokens as $i => $token) {
                if ('text' === $token[0]) {
                    $regex .= str_repeat(' ', $indent * 4).preg_quote($token[1], '#')."\n";
                } else {
                    if ($i >= $firstOptional) {
                        $regex .= str_repeat(' ', $indent * 4)."(?:\n";
                        ++$indent;
                    }
                    $regex .= str_repeat(' ', $indent * 4).
                        sprintf("%s(?P<%s>%s)\n", 
                        preg_quote($token[1], '#'), $token[3], $token[2]);
                }
            }
        }
        while (--$indent) {
            $regex .= str_repeat(' ', $indent * 4).")?\n";
        }

        // save variables
        $route['variables'] = $variables;

        // save compiled pattern
        $route['compiled'] = sprintf("#^\n%s$#xs", $regex);
        return $route;

#          return new CompiledRoute(
#              $route,
#              'text' === $tokens[0][0] ? $tokens[0][1] : '',
#              sprintf("#^\n%s$#xs", $regex),
#              array_reverse($tokens),
#              $variables
#          );
    
    }
}


