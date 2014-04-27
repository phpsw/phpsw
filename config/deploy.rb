set :application, "phpsw"
set :domain, "phpsw.org.uk"
set :deploy_to, "/var/www/#{fetch(:domain)}"
set :repo_url,  "git@github.com:phpsw/#{fetch(:application)}.git"
set :linked_files, %w{config/secrets.yml}

namespace :composer do
  desc "Copy vendors from previous release"
  task :copy_vendors do
    on roles :all, reject: lambda { |h| h.properties.no_release } do
      last_release = releases_path.join(capture(:ls, '-xr', releases_path).split[1])
      execute "if [ -d #{last_release}/vendor ]; then cp -a #{last_release}/vendor #{release_path}/vendor; fi"
    end
  end

  desc "Install"
  task :install do
    on roles :all do
      execute "composer install --no-dev --optimize-autoloader --prefer-source --working-dir #{release_path} --verbose"
    end
  end
end

namespace :mod_pagespeed do
  task :flush do
    on roles :all do
      execute "sudo touch /var/cache/mod_pagespeed/cache.flush"
    end
  end
end

namespace :varnish do
  task :restart do
    on roles :all do
      execute "sudo service varnish restart"
    end
  end
end

after "deploy:updating", "composer:copy_vendors"
after "deploy:updating", "composer:install"
after "deploy:finishing", "mod_pagespeed:flush"
after "deploy:finishing", "varnish:restart"
