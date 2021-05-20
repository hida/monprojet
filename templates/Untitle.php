for ($i = 0; $i < 3; $i ++) {
			$this->getLog()->debug('Starting export iteration '.$i);
			foreach ($rulesWithErrorCount as &$ruleWithStatus) {
			foreach ($rulesWithErrorCount as $idx => $ruleWithStatus) {
				$exportRule = $ruleWithStatus[1];

				if ($ruleWithStatus[0] === true) {
					continue;
				}
				try {
					$exportRule->setLastExecutionDate($time);
					$em->persist($exportRule);
					$em->flush();
				} catch (\Exception $e) {
					$this->getLog()->err($e->getMessage(), array('line' => $e->getLine(), 'file' => $e->getFile()));
				}
				try  {
					$export = new ExportArchive();
					$export->setServiceManager($this->getServiceLocator());
					$export->prepare($exportRule);
					$er = $exportRule->prepareExportRuleService();
					$ehs = $er->getExportHandler();
					$ehs->setExportArchive($export);
					$ehs->setOption('filepath', $export->getFilepath());
					if ($er->doSend()) {
						$this->getLog()->debug('export rule finished : ' . $exportRule->getId() . '::' . $exportRule->getName());
						// exclude rule from loop
						$ruleWithCount[0] = true;
						$rulesWithErrorCount[$idx][0] = true;
					}
				} catch (\Exception $e) {
					$this->getLog()->err($e->getMessage(), array('line' => $e->getLine(), 'file' => $e->getFile()));
				}
			}
		}
		return $this->response;
	}