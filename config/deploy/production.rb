server "52.30.232.72",
  user: "ubuntu",
  roles: %w[app web],
  ssh_options: {
    forward_agent: true,
    auth_methods: %w[publickey]
  }
