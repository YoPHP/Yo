<?php

namespace YoPHP;

use YoPHP\Path;
use YoPHP\Loader;
use YoPHP\Config;
use YoPHP\Url;

/**
 * 视图模板
 * @author YoPHP <admin@YoPHP.org>
 */
class View {

    //定义视图模板解析左标示
    protected $left = '{';
    //定义视图模板解析右标示
    protected $right = '}';
    //定义视图模板文件后缀
    protected $tplsuffix = '.tpl';
    //定义视图编译文件后缀
    protected $compilesuffix = '.php';
    //定义视图缓存文件后缀
    protected $cachesuffix = '.html';
    //定义视图模板是否运行插入PHP代码
    protected $php = false;
    //定义视图模板是否压缩html
    protected $compresshtml = false;
    //定义是否开启视图模板布局
    protected $layout = false;
    //定义是否开启视图模板布局入口文件名
    protected $layoutname = 'Public/layout';
    //定义视图模板输出替换变量
    protected $layoutitem = '{__REPLACE__}';
    //是否显示页面Trace信息
    protected $showtrace = false;
    //视图模板样式
    protected $theme = '';
    //编译目录
    protected $compiledir = '';
    // 模板变量
    private $_assign = [];

    public function __construct() {
        foreach (Config::get('view', []) as $key => $value) {
            if (isset($this->$key) && !is_null($value)) {
                $this->$key = $value;
            }
        }
        $this->setCompileDir(Config::get('view.compiledir') ?: __RUNTIME__ . DS . 'Compiledir');
    }

    /**
     * 渲染模板
     *
     * @param string $template 模板
     * @param mixed $data 赋值
     */
    public function render(string $template = null, $data = null) {
        !empty($data) && $this->assign($data);
        $this->fetch($template);
    }

    /**
     * 赋值
     * @param string|array $var
     * @param mixed $value
     */
    public function assign($var, $value = null) {
        is_array($var) ? ($this->_assign = array_merge($this->_assign, $var)) : ($this->_assign[$var] = $value);
    }

    /**
     * 获取模板变量
     * @param string $name
     * @return null|mixed
     */
    public function getAssign($name = '') {
        return $name === '' ? $this->_assign : ($this->_assign[$name] ?? null);
    }

    /**
     * 取得输出内容
     * @param string $template 模板
     * @param string $id 识别ID
     * @return string
     */
    public function fetch($template = null) {
        $this->getTemplateFile($template);
        if (empty($template) || !is_readable($template)) {
            return;
        }

        $compileFile = $this->getCompileFile($template);
        $this->compile($template, $compileFile);
        Loader::load($compileFile, $this->getAssign());
    }

    /**
     * 返回模板后缀
     * @return string
     */
    public function getSuffix(): string {
        return $this->tplsuffix;
    }

    /**
     * 检测是否支持
     * @param string  $template  模板
     * @param string $type
     * @return bool
     */
    public function supports(string $template, $type = null): bool {
        return in_array($type ?: pathinfo($template, PATHINFO_EXTENSION), [$type ?: 'tpl']);
    }

    /**
     * 设置编译目录
     * @param string $dir
     * @return $this
     */
    public function setCompileDir(string $dir) {
        $this->compiledir = $dir;
        return $this;
    }

    /**
     * 清除编译缓存
     * @access public
     * @return bool
     */
    public function clear() {
        return Path::clearDir($this->compiledir);
    }

    /**
     * 获取模板路径
     * @param string $template
     * @return string
     */
    private function getTemplateFile(&$template) {
        $template = str_replace(['/', '\\'], DS, $template);
        if (is_readable($template)) {
            return $template;
        }
        $template .= pathinfo($template, PATHINFO_EXTENSION) ? '' : $this->tplsuffix;

        if (!is_readable($template) && !is_dir(dirname($template))) {
            $template = Path::getDir("View/{$template}");
            //echo $template . PHP_EOL;
            if (!is_readable($template)) {
                $template = null;
            }
        }
        return $template;
        // && exit('Template Does Not Exist:' . $template);
    }

    /**
     * 解析模板名称
     * @param string $template
     * @return string
     */
    private function replaceTemplate(&$template) {
        $template = str_replace(['/', '\\'], DS, $template);
        return $template;
    }

    /**
     * 返回编辑文件
     * @param string $template
     * @return string
     */
    private function getCompileFile(string $template): string {
        return rtrim($this->compiledir, '/\\') . DS . $this->filename($template) . $this->compilesuffix;
    }

    /**
     * 取得存储文件名
     * @param string $name 文件名称
     * @return string
     */
    private function filename(string $name): string {
        $name = md5($name);
        return $name[0] . $name[1] . DS . $name[2] . $name[3] . DS . $name[4] . $name[5] . DS . $name;
    }

    private $_preg, $_replace, $_left, $_right;

    /**
     * 去掉UTF-8 Bom头
     * @param  string    $string
     * @return string
     */
    private function removeUTF8Bom($string): string {
        return substr($string, 0, 3) == pack('CCC', 239, 187, 191) ? substr($string, 3) : $string;
    }

    /**
     * 编译
     * @param string $template
     * @param string $compileFile
     */
    private function compile(string $template, string $compileFile) {
        if (!DEBUG && is_file($compileFile)) {
            return;
        }
        if (is_readable($template)) {
            $content = trim($this->removeUTF8Bom(file_get_contents($template)));
            $this->_left = '(?<!!)' . $this->stripPreg($this->left);
            $this->_right = '((?<![!]))' . $this->stripPreg($this->right);
            if ($this->layout) {
                $content = trim($this->parseLayout($content));
            }
            $content = $this->compileInclude($content);
            if (!is_file($compileFile) || ($md5 = md5($content)) !== file_get_contents($compileFile, true, null, 8, 32)) {
                if (!empty($content)) {
                    $this->compileCode($content);
                    $this->compresshtml && $this->compressHtml($content);
                }
                $dir = dirname($compileFile);
                Path::mkDir($dir) && is_writable($dir) && file_put_contents($compileFile, "<?php\n//" . ($md5 ?? md5($content)) . "\n?>" . $content);
            }
        }
    }

    /**
     * 清除编译
     * @return bool
     */
    public function clearCompile() {
        return Path::clearDir(rtrim($this->compiledir, '/\\'));
    }

    /**
     * 压缩HTML
     * @param string $content
     * @return string
     */
    private function compressHtml(&$content): string {
        $content = preg_replace(['/\?><\?php/', '~>\s+<~', '~>(\s+\n|\r)~', "/> *([^ ]*) *</", "/[\s]+/", "/<!--[^!]*-->/", "/ \"/", "'/\*[^*]*\*/'"], ['', '><', '>', ">\\1<", ' ', '', "\"", ''], $content);
        return $content;
    }

    /**
     * 解析布局
     * @param string $content
     */
    private function parseLayout(string $content): string {
        $layout = file_get_contents($this->getTemplateFile($this->layoutname));
        $layout = $this->compileInclude($layout);
        $pattern = '/' . $this->_left . 'block\sname=[\'"](.+?)[\'"]\s*?' . $this->_right . '(.*?)' . $this->_left . '\/block' . $this->_right . '/is';
        if (preg_match($pattern, $layout)) {
            preg_replace_callback($pattern, [$this, 'parseBlock'], $content);
            $layout = $this->replaceBlock($layout);
            return str_replace($this->layoutitem, preg_replace($pattern, '', $content), $layout);
        } else {
            return str_replace($this->layoutitem, $content, $layout);
        }
    }

    private $_block;

    /**
     * 记录当前页面中的block标签
     * @param string $name block名称
     * @return string
     */
    private function parseBlock($name): string {
        $this->_block[$name[1]] = $name[3];
        return '';
    }

    /**
     * 替换继承模板中的block标签
     * @param string $content 模板内容
     * @return string
     */
    private function replaceBlock($content): string {
        static $parse = 0;
        if (is_string($content)) {
            do {
                $content = empty($content) ? '' : preg_replace_callback('/(' . $this->_left . 'block\sname=[\'"](.+?)[\'"]\s*?' . $this->_right . ')(.*?)' . $this->_left . '\/block' . $this->_right . '/is', [$this, 'replaceBlock'], $content);
            } while ($parse && $parse--);
            return $content;
        } elseif (is_array($content)) {
            return $this->_block[$content[2]] ?? $content[4];
        }
    }

    /**
     * 编译导入文件
     * @param string $content
     */
    private function compileInclude($content): string {
        $content = empty($content) ? '' : preg_replace_callback('/' . $this->_left . 'include\sfile=[\'"](.+?)[\'"]\s*?' . $this->_right . '/is', [$this, 'parseInclude'], $content);
        return $content;
    }

    /**
     * 解析导入文件
     *
     * @param array $content
     * @return string
     */
    private function parseInclude($content): string {
        $template = stripslashes($content[1]);
        $this->replaceTemplate($template);
        $this->getTemplateFile($template);
        return $this->compileInclude($this->removeUTF8Bom(file_get_contents($template)));
    }

    /**
     * 转换标示符
     * @param string $tag
     * @return string
     */
    private function stripPreg($tag): string {
        return str_replace(['{', '}', '(', ')', '|', '[', ']', '-', '+', '*', '.', '^', '?'], ['\{', '\}', '\(', '\)', '\|', '\[', '\]', '\-', '\+', '\*', '\.', '\^', '\?'], $tag);
    }

    /**
     * 编译代码
     * @param string $content
     */
    private function compileCode(&$content) {
        $content = preg_replace_callback('/' . $this->_left . 'literal' . $this->_right . '(.*?)' . $this->_left . '\/literal' . $this->_right . '/is', [$this, 'parseLiteral'], $content);
        $this->compileVar($content);
        !$this->php && $this->replacePHP();
        $this->_preg();
        $this->_replace();
        $content = preg_replace($this->_preg, $this->_replace, $content);
        $content = str_replace(['!' . $this->_left, '!' . $this->_right], [$this->_left, $this->_right], $content);
        $content = preg_replace_callback('/<!--###literal(\d+)###-->/is', [$this, 'restoreLiteral'], $content);
        $content = preg_replace_callback("/##XML(.*?)XML##/s", [$this, 'xmlSubstitution'], $content);
    }

    private $_literal = [];

    /**
     * 替换页面中的literal标签
     * @param string $content 模板内容
     * @return string|false
     */
    private function parseLiteral($content) {
        if (is_array($content)) {
            $content = $content[2];
        }
        if (trim($content) == '') {
            return '';
        }
        $i = count($this->_literal);
        $parseStr = "<!--###literal{$i}###-->";
        $this->_literal[$i] = $content;
        return $parseStr;
    }

    /**
     * 还原被替换的literal标签
     * @param string $tag literal标签序号
     * @return string|false
     */
    private function restoreLiteral($tag) {
        if (is_array($tag)) {
            $tag = $tag[1];
        }
        $parseStr = $this->_literal[$tag];
        unset($this->_literal[$tag]);
        return $parseStr;
    }

    /**
     * 编译变量
     * @param string $content
     */
    private function compileVar(&$content) {
        $content = preg_replace_callback('/(' . $this->_left . ')([^\d\s].+?)(' . $this->_right . ')/is', [$this, 'parseTag'], $content);
        return $content;
    }

    /**
     * 解析标签
     * @param array $content
     * @return string
     */
    private function parseTag($content) {
        $content = preg_replace_callback('/\$\w+((\.\w+)*)?/', [$this, 'parseVar'], stripslashes($content[0]));
        return $content;
    }

    /**
     * 解析变量
     * @param array $var
     * @return string
     */
    private function parseVar($var) {
        if (empty($var[0])) {
            return '';
        }
        $vars = explode('.', $var[0]);
        $name = array_shift($vars);
        foreach ($vars as $val) {
            $name .= is_numeric($val) ? '[' . trim($val) . ']' : '["' . trim($val) . '"]';
        }
        return $name;
    }

    /**
     * 替换PHP标签
     */
    private function replacePHP() {
        $this->_preg[] = '/<\?(=|php|)(.+?)\?>/is';
        $this->_replace[] = '&lt;?\\1\\2?&gt;';
    }

    /**
     * 处理模板语法
     */
    private function _preg() {
        $this->_preg[] = '/' . $this->_left . '(else if|elseif) (.*?)' . $this->_right . '/i';
        $this->_preg[] = '/' . $this->_left . 'for (.*?)' . $this->_right . '/i';
        $this->_preg[] = '/' . $this->_left . 'while (.*?)' . $this->_right . '/i';
        $this->_preg[] = '/' . $this->_left . '(loop|foreach) (.*?)' . $this->_right . '/i';
        $this->_preg[] = '/' . $this->_left . 'if (.*?)' . $this->_right . '/i';
        $this->_preg[] = '/' . $this->_left . 'else' . $this->_right . '/i';
        $this->_preg[] = '/' . $this->_left . "(eval|_)( |[\r\n])(.*?)" . $this->_right . '/is';
        $this->_preg[] = '/' . $this->_left . ':(.*?)' . $this->_right . '/is';
        $this->_preg[] = '/' . $this->_left . '_e (.*?)' . $this->_right . '/is';
        $this->_preg[] = '/' . $this->_left . '_p (.*?)' . $this->_right . '/i';
        $this->_preg[] = '/' . $this->_left . '\/(if|for|loop|foreach|eval|while)' . $this->_right . '/i';
        $this->_preg[] = '/' . $this->_left . '(([_a-zA-Z][\w]*\(.*?\))|\$((\w+)(\[(\'|")?\$*\w*(\'|")?\])*(->)?(\w*)(\((\'|")?(.*?)(\'|")?\)|)))' . $this->_right . '/i';
        $this->_preg[] = "/(	| ){0,}(\r\n){1,}\";/";
        $this->_preg[] = '/' . $this->_left . '(\#|\*)(.*?)(\#|\*)' . $this->_right . '/';
        $this->_preg[] = '/' . $this->_left . '\@(.*?)' . $this->_right . '/';
        $this->_preg[] = '/' . $this->_left . '\#(.*?)' . $this->_right . '/';
    }

    /**
     * 模板语法替换
     */
    private function _replace() {
        $this->_replace[] = '<?php } else if (\\2) { ?>';
        $this->_replace[] = '<?php for (\\1) { ?>';
        $this->_replace[] = '<?php while (\\1) { ?>';
        $this->_replace[] = '<?php foreach (\\2) { ?>';
        $this->_replace[] = '<?php if (\\1) { ?>';
        $this->_replace[] = '<?php } else { ?>';
        $this->_replace[] = '<?php \\3; ?>';
        $this->_replace[] = '<?php echo \\1; ?>';
        $this->_replace[] = '<?php echo \\1; ?>';
        $this->_replace[] = '<?php print_r(\\1); ?>';
        $this->_replace[] = '<?php } ?>';
        $this->_replace[] = '<?php echo \\1; ?>';
        $this->_replace[] = '';
        $this->_replace[] = '';
        $this->_replace[] = '<?php Config::get("\\1");?>';
        $this->_replace[] = '<?php Url::get("\\1");?>';
    }

    /**
     * 处理XML
     * @param string $capture
     */
    private function xmlSubstitution($capture): string {
        return "<?php echo '<?xml " . stripslashes($capture[1]) . " ?>'; ?>";
    }

    public function __get($name) {
        return $this->getAssign($name);
    }

    public function __set($name, $value) {
        $this->assign($name, $value);
    }

}
