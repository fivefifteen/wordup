<?php
use function \Deployer\{
  currentHost,
  get,
  info,
  parse,
  runLocally,
  task,
  upload
};

task('templates:render', function () {
  $m = new Mustache_Engine;
  $current_host = currentHost();
  $current_host_alias = $current_host->getAlias();
  $remote_deploy = !\WordUp\Helper::isLocalHost();
  $templates_files = get('templates/files');

  foreach($templates_files as $rendered_file => $template_file) {
    if (!is_string($rendered_file)) {
      $rendered_file = \WordUp\Helper::getTemplateRenderedName($template_file);
    }

    if (
      !($template_stage = \WordUp\Helper::getTemplateStage($template_file)) ||
      (($specific_stage = is_string($template_stage)) && $template_stage !== $current_host_alias) ||
      (!$specific_stage && array_search("{$rendered_file}.{$current_host_alias}.mustache", $templates_files) !== false)
    ) {
      info("Skipping {$template_file}...");
      continue;
    }

    info("Loading {$template_file}...");

    $contents = file_get_contents($template_file);
    $rendered = $m->render($contents, (array) $current_host);
    $remote_path = "{{current_path}}/$rendered_file";

    if ($remote_deploy) {
      $rendered_file = "{{templates/temp_dir}}/{$rendered_file}";
      runLocally('mkdir -p {{templates/temp_dir}}');
    }

    $rendered_file = parse($rendered_file);

    info("Saving {$rendered_file}...");

    file_put_contents($rendered_file, $rendered);

    if ($remote_deploy) {
      upload($rendered_file, $remote_path);
    }
  }
})->desc('Renders mustache template files');
?>