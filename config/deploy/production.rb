server "app1.phpsw.uk",
  user: "ubuntu",
  roles: %w[app web],
  ssh_options: {
    forward_agent: true,
    auth_methods: %w[publickey]
  }

server "app2.phpsw.uk",
  user: "ubuntu",
  roles: %w[app web],
  ssh_options: {
    forward_agent: true,
    auth_methods: %w[publickey]
  }
