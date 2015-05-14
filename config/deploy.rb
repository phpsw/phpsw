set :application, "phpsw"
set :domain, "phpsw.uk"
set :deploy_to, "/var/www/#{fetch(:domain)}"
set :repo_url,  "git@github.com:phpsw/#{fetch(:application)}.git"
set :linked_dirs, %w{web/slides}
set :linked_files, %w{config/secrets.yml}

namespace :composer do
  task :install do
    on roles :all do
      execute "composer install --no-dev --optimize-autoloader --prefer-source --working-dir #{release_path} --verbose"
    end
  end

  after "deploy:updating", :install
end

namespace :npm do
  task :install do
    on roles :app do
      within release_path do
        execute :npm, :install, "--quiet"
      end
    end
  end

  before :install, :copy do
    on roles :app, reject: lambda { |h| h.properties.no_release } do
      last_release = releases_path.join(capture(:ls, "-xr", releases_path).split[1])
      execute "if [ -d #{last_release}/node_modules ]; then cp -a #{last_release}/node_modules #{release_path}/node_modules; fi"
    end
  end

  after "deploy:updating", :install
end

namespace :bower do
  task :install do
    on roles :app do
      within release_path do
        with path: "#{release_path}/node_modules/.bin:$PATH" do
          execute :bower, :install, "--quiet"
        end
      end
    end
  end

  after "npm:install", :install
end

namespace :gulp do
  task :build do
    on roles :app do
      within release_path do
        with node_env: fetch(:env), path: "#{release_path}/node_modules/.bin:$PATH" do
          execute :gulp, :build
        end
      end
    end
  end

  after "bower:install", :build
end

namespace :sf do
  task :import do
    on roles :app do
      execute "#{release_path}/app/console joindin:import:all"
      execute "#{release_path}/app/console youtube:import:all"
      execute "#{release_path}/app/console meetup:import:all"
    end
  end

  after "deploy:finishing", :import
end

namespace :twitter do
  task :import do
    on roles :app do
      execute "#{release_path}/app/console twitter:import:all"
    end
  end

  after "deploy:finishing", :import
end

namespace :mod_pagespeed do
  task :flush do
    on roles :all do
      execute "sudo touch /var/cache/mod_pagespeed/cache.flush"
    end
  end

  after "deploy:finishing", :flush
end

namespace :varnish do
  task :restart do
    on roles :all do
      execute "sudo service varnish restart"
    end
  end

  after "deploy:finishing", :restart
end
